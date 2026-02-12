<?php declare(strict_types=1);

namespace Nette\PHPStan\Php;

use PhpParser\Node\Expr;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PHPStan\Analyser\ArgumentsNormalizer;
use PHPStan\Analyser\Scope;
use PHPStan\DependencyInjection\Type\DynamicReturnTypeExtensionRegistryProvider;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Reflection\ReflectionProvider;
use PHPStan\Type\Constant\ConstantBooleanType;
use PHPStan\Type\ExpressionTypeResolverExtension;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use function array_merge, explode, str_contains, strtolower;


/**
 * Removes false from return types of native PHP functions and methods
 * where false is trivial or outdated.
 */
class RemoveFailingReturnTypeExtension implements ExpressionTypeResolverExtension
{
	/** @var array<string, true> */
	private array $functions = [];

	/** @var array<lowercase-string, array<lowercase-string, true>> */
	private array $methods = [];


	/**
	 * @param list<string> $items  entries without '::' are functions, with '::' are Class::method
	 */
	public function __construct(
		array $items,
		private DynamicReturnTypeExtensionRegistryProvider $registryProvider,
		private ReflectionProvider $reflectionProvider,
	) {
		foreach ($items as $item) {
			if (str_contains($item, '::')) {
				[$class, $method] = explode('::', $item, 2);
				$this->methods[strtolower($class)][strtolower($method)] = true;
			} else {
				$this->functions[$item] = true;
			}
		}
	}


	public function getType(Expr $expr, Scope $scope): ?Type
	{
		if ($expr instanceof FuncCall) {
			return $this->resolveFuncCall($expr, $scope);
		}

		if ($expr instanceof MethodCall) {
			return $this->resolveMethodCall($expr, $scope);
		}

		if ($expr instanceof StaticCall) {
			return $this->resolveStaticCall($expr, $scope);
		}

		return null;
	}


	private function resolveFuncCall(FuncCall $expr, Scope $scope): ?Type
	{
		if (!$expr->name instanceof Name) {
			return null;
		}

		if (!$this->reflectionProvider->hasFunction($expr->name, $scope)) {
			return null;
		}

		$functionReflection = $this->reflectionProvider->getFunction($expr->name, $scope);
		if (!isset($this->functions[$functionReflection->getName()])) {
			return null;
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$expr->getArgs(),
			$functionReflection->getVariants(),
			$functionReflection->getNamedArgumentsVariants(),
		);

		$normalizedCall = ArgumentsNormalizer::reorderFuncArguments($parametersAcceptor, $expr);
		if ($normalizedCall === null) {
			return self::removeFalse($parametersAcceptor->getReturnType());
		}

		$registry = $this->registryProvider->getRegistry();
		foreach ($registry->getDynamicFunctionReturnTypeExtensions($functionReflection) as $extension) {
			$type = $extension->getTypeFromFunctionCall($functionReflection, $normalizedCall, $scope);
			if ($type !== null) {
				return self::removeFalse($type);
			}
		}

		return self::removeFalse($parametersAcceptor->getReturnType());
	}


	private function resolveMethodCall(MethodCall $expr, Scope $scope): ?Type
	{
		if (!$expr->name instanceof Identifier) {
			return null;
		}

		$methodName = $expr->name->name;
		$callerType = $scope->getType($expr->var);

		if (!$this->isMethodSupportedByType($callerType, $methodName)) {
			return null;
		}

		$methodReflection = $scope->getMethodReflection($callerType, $methodName);
		if ($methodReflection === null) {
			return null;
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$expr->getArgs(),
			$methodReflection->getVariants(),
			$methodReflection->getNamedArgumentsVariants(),
		);

		$normalizedCall = ArgumentsNormalizer::reorderMethodArguments($parametersAcceptor, $expr);
		if ($normalizedCall === null) {
			return self::removeFalse($parametersAcceptor->getReturnType());
		}

		$resolvedTypes = [];
		$registry = $this->registryProvider->getRegistry();
		foreach ($callerType->getObjectClassNames() as $className) {
			foreach ($registry->getDynamicMethodReturnTypeExtensionsForClass($className) as $extension) {
				if (!$extension->isMethodSupported($methodReflection)) {
					continue;
				}

				$type = $extension->getTypeFromMethodCall($methodReflection, $normalizedCall, $scope);
				if ($type !== null) {
					$resolvedTypes[] = $type;
				}
			}
		}

		if ($resolvedTypes !== []) {
			return self::removeFalse(TypeCombinator::union(...$resolvedTypes));
		}

		return self::removeFalse($parametersAcceptor->getReturnType());
	}


	private function resolveStaticCall(StaticCall $expr, Scope $scope): ?Type
	{
		if (!$expr->name instanceof Identifier) {
			return null;
		}

		$methodName = $expr->name->name;

		if ($expr->class instanceof Name) {
			$callerType = $scope->resolveTypeByName($expr->class);
		} else {
			$callerType = TypeCombinator::removeNull($scope->getType($expr->class))
				->getObjectTypeOrClassStringObjectType();
		}

		if (!$this->isMethodSupportedByType($callerType, $methodName)) {
			return null;
		}

		$methodReflection = $scope->getMethodReflection($callerType, $methodName);
		if ($methodReflection === null) {
			return null;
		}

		$parametersAcceptor = ParametersAcceptorSelector::selectFromArgs(
			$scope,
			$expr->getArgs(),
			$methodReflection->getVariants(),
			$methodReflection->getNamedArgumentsVariants(),
		);

		$normalizedCall = ArgumentsNormalizer::reorderStaticCallArguments($parametersAcceptor, $expr);
		if ($normalizedCall === null) {
			return self::removeFalse($parametersAcceptor->getReturnType());
		}

		$resolvedTypes = [];
		$registry = $this->registryProvider->getRegistry();
		foreach ($callerType->getObjectClassNames() as $className) {
			foreach ($registry->getDynamicStaticMethodReturnTypeExtensionsForClass($className) as $extension) {
				if (!$extension->isStaticMethodSupported($methodReflection)) {
					continue;
				}

				$type = $extension->getTypeFromStaticMethodCall($methodReflection, $normalizedCall, $scope);
				if ($type !== null) {
					$resolvedTypes[] = $type;
				}
			}
		}

		if ($resolvedTypes !== []) {
			return self::removeFalse(TypeCombinator::union(...$resolvedTypes));
		}

		return self::removeFalse($parametersAcceptor->getReturnType());
	}


	private function isMethodSupportedByType(Type $callerType, string $methodName): bool
	{
		$lowerMethod = strtolower($methodName);
		foreach ($callerType->getObjectClassNames() as $className) {
			if (!$this->reflectionProvider->hasClass($className)) {
				continue;
			}

			$classReflection = $this->reflectionProvider->getClass($className);
			$classesToCheck = array_merge(
				[$classReflection->getName()],
				$classReflection->getParentClassesNames(),
				$classReflection->getNativeReflection()->getInterfaceNames(),
			);

			foreach ($classesToCheck as $ancestorName) {
				if (isset($this->methods[strtolower($ancestorName)][$lowerMethod])) {
					return true;
				}
			}
		}

		return false;
	}


	private static function removeFalse(Type $type): Type
	{
		return TypeCombinator::remove($type, new ConstantBooleanType(false));
	}
}
