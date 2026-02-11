<?php declare(strict_types=1);

namespace Tester {
	class Assert
	{
		/** @param \Closure(): void $function */
		public static function exception(\Closure $function, string $class): void
		{
		}


		/** @param callable(): void $function */
		public static function noError(callable $function): void
		{
		}
	}
}

namespace {
	/** @param Closure(): void $function */
	function testException(string $description, Closure $function, string $class): void
	{
	}


	/** @param Closure(): void $function */
	function testNoError(string $description, Closure $function): void
	{
	}


	testException('test', fn() => strlen('hello'), TypeError::class);
	testNoError('test', fn() => strlen('hello'));

	Tester\Assert::exception(fn() => strlen('hello'), TypeError::class);
	Tester\Assert::noError(fn() => strlen('hello'));
}
