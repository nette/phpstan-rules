<?php declare(strict_types=1);

use Nette\Utils\Helpers;
use function PHPStan\Testing\assertType;


// false → null
assertType('null', Helpers::falseToNull(false));

// no false in type → unchanged
assertType("'hello'", Helpers::falseToNull('hello'));
assertType('123', Helpers::falseToNull(123));


function testStringFalse(string|false $value): void
{
	assertType('string|null', Helpers::falseToNull($value));
}


function testIntFalse(int|false $value): void
{
	assertType('int|null', Helpers::falseToNull($value));
}


function testStringFalseNull(string|false|null $value): void
{
	assertType('string|null', Helpers::falseToNull($value));
}


function testBool(bool $value): void
{
	assertType('true|null', Helpers::falseToNull($value));
}


function testNoFalse(int $value): void
{
	assertType('int', Helpers::falseToNull($value));
}
