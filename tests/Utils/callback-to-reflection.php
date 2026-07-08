<?php declare(strict_types=1);

use Nette\Utils\Callback;

class Foo
{
	/** @param array{string, string} $entity */
	public function fromTuple(array $entity): void
	{
		Callback::toReflection($entity);
	}


	public function fromString(string $entity): void
	{
		Callback::toReflection($entity);
	}
}
