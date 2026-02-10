<?php declare(strict_types=1);

use function PHPStan\Testing\assertType;


class TestClassDefinedInFile
{
	public function getValue(): int
	{
		return 42;
	}
}


class TestChildClass extends TestClassDefinedInFile
{
	public function getExtra(): string
	{
		return 'extra';
	}
}


function testCustomClassMethod(TestClassDefinedInFile $obj): void
{
	assertType('int', $obj->getValue());
}


function testChildClassMethod(TestChildClass $obj): void
{
	assertType('int', $obj->getValue());
	assertType('string', $obj->getExtra());
}


function testCustomClassType(TestClassDefinedInFile $obj): void
{
	assertType(TestClassDefinedInFile::class, $obj);
}
