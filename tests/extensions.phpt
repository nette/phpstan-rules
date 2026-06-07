<?php declare(strict_types=1);

require __DIR__ . '/bootstrap.php';

use Nette\PHPStan\Tester\TypeAssert;

// Php
TypeAssert::assertTypes(__DIR__ . '/Php/failing-return-type.php');
TypeAssert::assertNoErrors(__DIR__ . '/Php/arrow-function-void.php', [__DIR__ . '/Php/arrow-function-void.neon']);
TypeAssert::assertNoErrors(__DIR__ . '/Php/closure-type-check.php');
TypeAssert::assertTypes(__DIR__ . '/Php/interface-property-tag.php');
TypeAssert::assertNoErrors(__DIR__ . '/Php/interface-property-tag-clean.php');

// Application
TypeAssert::assertErrors(__DIR__ . '/Application/rethrow-abort-exception.php', [
	'nette.abortException on line 14',
	'nette.abortException on line 85',
]);

// Assets
TypeAssert::assertTypes(__DIR__ . '/Assets/get-mapper-return-type.php', [__DIR__ . '/Assets/assets-mapper-mapping.neon']);
TypeAssert::assertTypes(__DIR__ . '/Assets/mapper-get-asset-return-type.php', [__DIR__ . '/Assets/assets-mapper-mapping.neon']);
TypeAssert::assertTypes(__DIR__ . '/Assets/registry-get-asset-return-type.php', [__DIR__ . '/Assets/assets-mapper-mapping.neon']);

// ComponentModel
TypeAssert::assertTypes(__DIR__ . '/ComponentModel/get-component-return-type.php');

// Database
TypeAssert::assertTypes(__DIR__ . '/Database/explorer-table-return-type.php', [__DIR__ . '/Database/database-row-mapping.neon']);
TypeAssert::assertTypes(__DIR__ . '/Database/active-row-related-return-type.php', [__DIR__ . '/Database/database-row-mapping.neon']);
TypeAssert::assertTypes(__DIR__ . '/Database/active-row-ref-return-type.php', [__DIR__ . '/Database/database-row-mapping.neon']);
TypeAssert::assertTypes(__DIR__ . '/Database/selection-insert-return-type.php', [__DIR__ . '/Database/database-row-mapping.neon']);

// DI
TypeAssert::assertNoErrors(__DIR__ . '/DI/inject-property.php');

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
TypeAssert::assertTypes(__DIR__ . '/Utils/strings-replace-closure.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/strings-match-narrowing.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/arrays-invoke-return-type.php');
TypeAssert::assertNoErrors(__DIR__ . '/Utils/callback-to-reflection.php');
TypeAssert::assertTypes(__DIR__ . '/Utils/html-virtual-members.php');
TypeAssert::assertErrors(__DIR__ . '/Utils/valid-regular-expression.php', [
	'nette.strings.regexpPattern on line 22',
	'nette.strings.regexpPattern on line 23',
	'nette.strings.regexpPattern on line 24',
	'nette.strings.regexpPattern on line 25',
	'nette.strings.regexpPattern on line 26',
]);
