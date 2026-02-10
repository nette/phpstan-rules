<?php declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Nette\PHPStan\Tester\TypeAssert;

// Tester
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-in-function.php');
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-type-with-custom-class.php');
