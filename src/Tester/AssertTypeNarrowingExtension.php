<?php declare(strict_types=1);

namespace Nette\PHPStan\Tester;

use PhpParser\Node\{Arg, Expr, Name};
use PhpParser\Node\Expr\BinaryOp\{BooleanAnd, BooleanOr, Identical, NotIdentical};
use PhpParser\Node\Expr\{BooleanNot, ConstFetch, FuncCall, Instanceof_, StaticCall};
use PHPStan\Analyser\{Scope, SpecifiedTypes, TypeSpecifier, TypeSpecifierAwareExtension, TypeSpecifierContext};
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\StaticMethodTypeSpecifyingExtension;
use Tester\Assert;
use function count;


/**
 * Narrows variable types after Tester\Assert assertion calls.
 */
class AssertTypeNarrowingExtension implements StaticMethodTypeSpecifyingExtension, TypeSpecifierAwareExtension
{
	private TypeSpecifier $typeSpecifier;


	public function setTypeSpecifier(TypeSpecifier $typeSpecifier): void
	{
		$this->typeSpecifier = $typeSpecifier;
	}


	public function getClass(): string
	{
		return Assert::class;
	}


	public function isStaticMethodSupported(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		TypeSpecifierContext $context,
	): bool
	{
		$minArgs = match ($staticMethodReflection->getName()) {
			'null', 'notNull', 'true', 'false', 'truthy', 'falsey', 'nan' => 1,
			'same', 'notSame', 'type', 'count', 'hasKey', 'hasNotKey' => 2,
			default => null,
		};
		if ($minArgs === null || $node->isFirstClassCallable() || count($node->getArgs()) < $minArgs) {
			return false;
		}

		// named arguments are left unresolved only in an invalid call
		foreach (array_slice($node->getArgs(), 0, $minArgs) as $arg) {
			if ($arg->unpack || $arg->name !== null) {
				return false;
			}
		}

		return true;
	}


	public function specifyTypes(
		MethodReflection $staticMethodReflection,
		StaticCall $node,
		Scope $scope,
		TypeSpecifierContext $context,
	): SpecifiedTypes
	{
		$args = $node->getArgs();
		$expression = match ($staticMethodReflection->getName()) {
			'null' => new Identical($args[0]->value, new ConstFetch(new Name('null'))),
			'notNull' => new NotIdentical($args[0]->value, new ConstFetch(new Name('null'))),
			'true' => new Identical($args[0]->value, new ConstFetch(new Name('true'))),
			'false' => new Identical($args[0]->value, new ConstFetch(new Name('false'))),
			'truthy' => $args[0]->value,
			'falsey' => new BooleanNot($args[0]->value),
			'nan' => new BooleanAnd(
				new FuncCall(new Name('is_float'), [$args[0]]),
				new FuncCall(new Name('is_nan'), [$args[0]]),
			),
			'same' => new Identical($args[1]->value, $args[0]->value),
			'notSame' => new NotIdentical($args[1]->value, $args[0]->value),
			'type' => $this->createTypeExpression($scope, $args),
			'count' => new BooleanAnd(
				new BooleanOr(
					new FuncCall(new Name('is_array'), [$args[1]]),
					new Instanceof_($args[1]->value, new Name(\Countable::class)),
				),
				new Identical(new FuncCall(new Name('count'), [$args[1]]), $args[0]->value),
			),
			'hasKey' => new FuncCall(new Name('array_key_exists'), [$args[0], $args[1]]),
			'hasNotKey' => new BooleanNot(new FuncCall(new Name('array_key_exists'), [$args[0], $args[1]])),
			default => null,
		};

		if ($expression === null) {
			return new SpecifiedTypes([], []);
		}

		return $this->typeSpecifier->specifyTypesInCondition(
			$scope,
			$expression,
			TypeSpecifierContext::createTruthy(),
		)->setRootExpr($expression);
	}


	/**
	 * Type name strings (a union of them means any of the types) or an object (instanceof its class).
	 * @param  Arg[]  $args
	 */
	private function createTypeExpression(Scope $scope, array $args): ?Expr
	{
		$typeType = $scope->getType($args[0]->value);
		$checks = [];

		if ($typeType->isObject()->yes()) {
			foreach ($typeType->getObjectClassNames() as $className) {
				$checks[] = new Instanceof_($args[1]->value, new Name($className));
			}
		} else {
			foreach ($typeType->getConstantStrings() as $constantString) {
				$check = $this->createTypeNameCheck($constantString->getValue(), $args[1]);
				if ($check === null) {
					return null;
				}
				$checks[] = $check;
			}
		}

		if (!$checks) {
			return null;
		}

		$expression = array_shift($checks);
		foreach ($checks as $check) {
			$expression = new BooleanOr($expression, $check);
		}
		return $expression;
	}


	private function createTypeNameCheck(string $typeName, Arg $value): ?Expr
	{
		if ($typeName === 'list') {
			return new BooleanAnd(
				new FuncCall(new Name('is_array'), [$value]),
				new FuncCall(new Name('array_is_list'), [$value]),
			);
		}

		$func = match ($typeName) {
			'array' => 'is_array',
			'bool' => 'is_bool',
			'callable' => 'is_callable',
			'float' => 'is_float',
			'int', 'integer' => 'is_int',
			'null' => 'is_null',
			'object' => 'is_object',
			'resource' => 'is_resource',
			'scalar' => 'is_scalar',
			'string' => 'is_string',
			default => null,
		};
		if ($func !== null) {
			return new FuncCall(new Name($func), [$value]);
		}

		$className = ltrim($typeName, '\\');
		return $className === ''
			? null
			: new Instanceof_($value->value, new Name($className));
	}
}
