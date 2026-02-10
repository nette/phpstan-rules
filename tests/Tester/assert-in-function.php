<?php declare(strict_types=1);

use function PHPStan\Testing\assertType;


function testAssertTypeInsideFunction(): void
{
	assertType('1', 1);
}
