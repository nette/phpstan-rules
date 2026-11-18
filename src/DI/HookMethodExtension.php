<?php declare(strict_types=1);

namespace Nette\PHPStan\DI;

use Nette\DI\CompilerExtension;
use PHPStan\Reflection\ExtendedMethodReflection;
use PHPStan\Rules\Methods\AlwaysUsedMethodExtension;


/**
 * Treats methods of a compiler extension marked with #[Nette\DI\Attributes\Hook]
 * as used, because Nette finds them by reflection and calls them as phase handlers.
 * Without this, PHPStan reports such private methods as unused.
 */
final class HookMethodExtension implements AlwaysUsedMethodExtension
{
	private const HookAttribute = 'Nette\DI\Attributes\Hook';


	public function isAlwaysUsed(ExtendedMethodReflection $methodReflection): bool
	{
		if (!$methodReflection->getDeclaringClass()->is(CompilerExtension::class)) {
			return false;
		}

		foreach ($methodReflection->getAttributes() as $attribute) {
			if ($attribute->getName() === self::HookAttribute) {
				return true;
			}
		}

		return false;
	}
}
