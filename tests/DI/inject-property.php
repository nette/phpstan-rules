<?php declare(strict_types=1);

namespace Tests\DI;

use Nette\DI\Attributes\Inject;

class InjectedService
{
}

class SomePresenter
{
	#[Inject]
	public InjectedService $service;


	public function run(): InjectedService
	{
		return $this->service;
	}
}
