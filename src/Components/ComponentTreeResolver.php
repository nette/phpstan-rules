<?php declare(strict_types=1);

namespace Nette\PHPStan\Components;

use PhpParser\Node\Expr;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\Type;
use function array_pop, ucfirst;


/**
 * Resolves the type of a named child component within a Nette component tree
 * via createComponent<Name>() factory methods.
 *
 * Supports single-level access, chained access ($container['a']['b']) and the dash
 * notation ($container['a-b']) that Nette expands to nested getComponent() calls.
 */
final class ComponentTreeResolver
{
	/**
	 * Chained access: $prefixExpr['child']. The prefix's static type already carries
	 * the container class, so the child is resolved from its createComponent factory.
	 */
	public function resolveChainedChild(Expr $prefixExpr, string $childName, Scope $scope): ?Type
	{
		return $this->resolveChildOf($scope->getType($prefixExpr), null, $childName, $scope);
	}


	/**
	 * Dash notation: $caller['seg1-seg2-...-child']. Each prefix segment is resolved
	 * via a createComponent factory on the previous container's class.
	 * @param  string[]  $segments
	 */
	public function resolveDashPath(Expr $caller, array $segments, Scope $scope): ?Type
	{
		$type = $scope->getType($caller);
		$factory = null;

		$prefix = $segments;
		$last = array_pop($prefix);
		if ($last === null) {
			return null;
		}

		foreach ($prefix as $segment) {
			$factoryName = 'createComponent' . ucfirst($segment);
			if (!$type->hasMethod($factoryName)->yes()) {
				return null;
			}

			$factory = $type->getMethod($factoryName, $scope);
			$type = $factory->getVariants()[0]->getReturnType();
		}

		return $this->resolveChildOf($type, $factory, $last, $scope);
	}


	/**
	 * Resolves a child component within a container from its createComponent<Name>() factory.
	 */
	public function resolveChildOf(Type $containerType, ?MethodReflection $factory, string $name, Scope $scope): ?Type
	{
		$factoryName = 'createComponent' . ucfirst($name);
		if ($containerType->hasMethod($factoryName)->yes()) {
			return $containerType->getMethod($factoryName, $scope)->getVariants()[0]->getReturnType();
		}

		return null;
	}
}
