<?php declare(strict_types=1);

namespace Nette\DI\Attributes {
	use Nette\DI\Phase;

	// nette/di < 3.3 lacks the attribute
	if (!class_exists(Hook::class)) {
		#[\Attribute(\Attribute::TARGET_METHOD)]
		class Hook
		{
			public function __construct(
				public Phase $phase,
			) {
			}
		}
	}
}

namespace Nette\DI {
	if (!enum_exists(Phase::class)) {
		enum Phase
		{
			case Register;
		}
	}
}

namespace Tests\DI {
	use Nette\DI\Attributes\Hook;
	use Nette\DI\{CompilerExtension, ContainerBuilder, Phase};


	final class HookExtension extends CompilerExtension
	{
		#[Hook(Phase::Register)]
		private function registerServices(ContainerBuilder $builder): void
		{
		}


		private function unused(): void
		{
		}


		#[\ReturnTypeWillChange]
		private function otherAttribute(): void
		{
		}
	}


	final class NotExtension
	{
		#[Hook(Phase::Register)]
		private function registerServices(ContainerBuilder $builder): void
		{
		}
	}
}
