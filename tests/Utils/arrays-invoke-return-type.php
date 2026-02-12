<?php declare(strict_types=1);

use Nette\Utils\Arrays;
use function PHPStan\Testing\assertType;


// invoke() — typed closures


/** @param array<callable(): string> $callbacks */
function testInvokeString(array $callbacks): void
{
	assertType('array<string>', Arrays::invoke($callbacks));
}


/** @param array<callable(): int> $callbacks */
function testInvokeInt(array $callbacks): void
{
	assertType('array<int>', Arrays::invoke($callbacks));
}


// invoke() — string-keyed array preserves keys


/** @param array<string, callable(): string> $callbacks */
function testInvokeStringKeys(array $callbacks): void
{
	assertType('array<string, string>', Arrays::invoke($callbacks));
}


// invoke() — forwarding args


/** @param array<callable(string): string> $callbacks */
function testInvokeForwardArgs(array $callbacks): void
{
	assertType('array<string>', Arrays::invoke($callbacks, 'hello'));
}


/** @param array<callable(int, int): int> $callbacks */
function testInvokeForwardMultipleArgs(array $callbacks): void
{
	assertType('array<int>', Arrays::invoke($callbacks, 1, 2));
}


// invoke() — callable(): void


/** @param list<callable(): void> $callbacks */
function testInvokeVoid(array $callbacks): void
{
	assertType('array<int<0, max>, null>', Arrays::invoke($callbacks));
}


// invoke() — typed callable parameter


/** @param array<string, callable(): bool> $callbacks */
function testInvokeTypedCallable(array $callbacks): void
{
	assertType('array<string, bool>', Arrays::invoke($callbacks));
}


// invokeMethod() — known class and method


/** @param array<DateTimeImmutable> $objects */
function testInvokeMethodTimestamp(array $objects): void
{
	assertType('array<int>', Arrays::invokeMethod($objects, 'getTimestamp'));
}


// invokeMethod() — string keys preserved


/** @param array<string, DateTimeImmutable> $objects */
function testInvokeMethodStringKeys(array $objects): void
{
	assertType('array<string, int>', Arrays::invokeMethod($objects, 'getTimestamp'));
}


// invokeMethod() — forwarding args


/** @param array<DateTimeImmutable> $objects */
function testInvokeMethodFormat(array $objects): void
{
	assertType('array<string>', Arrays::invokeMethod($objects, 'format', 'Y-m-d'));
}
