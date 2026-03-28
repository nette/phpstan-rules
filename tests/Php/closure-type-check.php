<?php declare(strict_types=1);

class Foo
{
	/** @param string[] $items */
	public function setItems(array $items): void
	{
		(function (string ...$items) {})(...$items);
	}
}
