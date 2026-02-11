<?php declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Nette\PHPStan\Tester\TypeAssert;

// Php
TypeAssert::assertTypes(__DIR__ . '/Php/failing-return-type.php');
TypeAssert::assertNoErrors(__DIR__ . '/Php/closure-type-check.php');

// Schema
TypeAssert::assertTypes(__DIR__ . '/Schema/expect-array-return-type.php');

// Tester
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-in-function.php');
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-type-with-custom-class.php');
