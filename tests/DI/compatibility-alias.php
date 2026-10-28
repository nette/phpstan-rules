<?php declare(strict_types=1);

namespace Tests\DI;

use Nette\DI\{Definitions, Statement};


function createStatement(): Definitions\Statement
{
	return new Statement('Foo', ['bar']);
}
