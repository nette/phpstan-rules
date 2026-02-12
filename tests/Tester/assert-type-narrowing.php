<?php declare(strict_types=1);

use Tester\Assert;
use function PHPStan\Testing\assertType;


function testNull(int|string|null $val): void
{
	Assert::null($val);
	assertType('null', $val);
}


function testNotNull(int|string|null $val): void
{
	Assert::notNull($val);
	assertType('int|string', $val);
}


function testTrue(mixed $val): void
{
	Assert::true($val);
	assertType('true', $val);
}


function testFalse(mixed $val): void
{
	Assert::false($val);
	assertType('false', $val);
}


function testTruthy(stdClass|null $val): void
{
	Assert::truthy($val);
	assertType('stdClass', $val);
}


function testFalsey(bool $val): void
{
	Assert::falsey($val);
	assertType('false', $val);
}


function testSame(int|string $val): void
{
	Assert::same(42, $val);
	assertType('42', $val);
}


function testNotSame(int|null $val): void
{
	Assert::notSame(null, $val);
	assertType('int', $val);
}


function testTypeString(mixed $val): void
{
	Assert::type('string', $val);
	assertType('string', $val);
}


function testTypeInt(mixed $val): void
{
	Assert::type('int', $val);
	assertType('int', $val);
}


function testTypeFloat(mixed $val): void
{
	Assert::type('float', $val);
	assertType('float', $val);
}


function testTypeBool(mixed $val): void
{
	Assert::type('bool', $val);
	assertType('bool', $val);
}


function testTypeArray(mixed $val): void
{
	Assert::type('array', $val);
	assertType('array<mixed, mixed>', $val);
}


function testTypeCallable(mixed $val): void
{
	Assert::type('callable', $val);
	assertType('callable(): mixed', $val);
}


function testTypeObject(mixed $val): void
{
	Assert::type('object', $val);
	assertType('object', $val);
}


function testTypeScalar(mixed $val): void
{
	Assert::type('scalar', $val);
	assertType('bool|float|int|string', $val);
}


function testTypeNull(int|string|null $val): void
{
	Assert::type('null', $val);
	assertType('null', $val);
}


function testTypeList(mixed $val): void
{
	Assert::type('list', $val);
	assertType('array<mixed, mixed>', $val);
}


function testTypeClass(mixed $val): void
{
	Assert::type(stdClass::class, $val);
	assertType('stdClass', $val);
}


function testTypeInterface(mixed $val): void
{
	Assert::type(Countable::class, $val);
	assertType('Countable', $val);
}
