<?php declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Nette\PHPStan\Tester\TypeAssert;

// Php
TypeAssert::assertTypes(__DIR__ . '/Php/failing-return-type.php');
TypeAssert::assertNoErrors(__DIR__ . '/Php/arrow-function-void.php', [__DIR__ . '/Php/arrow-function-void.neon']);
TypeAssert::assertNoErrors(__DIR__ . '/Php/closure-type-check.php');

// ComponentModel
TypeAssert::assertTypes(__DIR__ . '/ComponentModel/get-component-return-type.php');

// Forms
TypeAssert::assertTypes(__DIR__ . '/Forms/form-component-return-type.php');

// Schema
TypeAssert::assertTypes(__DIR__ . '/Schema/expect-array-return-type.php');

// Tester
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-type-narrowing.php');
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-in-function.php');
TypeAssert::assertTypes(__DIR__ . '/Tester/assert-type-with-custom-class.php');

// Utils
TypeAssert::assertTypes(__DIR__ . '/Utils/false-to-null-return-type.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/strings-return-type.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/arrays-invoke-return-type.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/html-virtual-members.php');
