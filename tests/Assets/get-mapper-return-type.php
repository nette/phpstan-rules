<?php declare(strict_types=1);

use Nette\Assets\Registry;
use function PHPStan\Testing\assertType;


class CustomMapper implements Nette\Assets\Mapper
{
	public function getAsset(string $reference, array $options = []): Nette\Assets\Asset
	{
		throw new Nette\Assets\AssetNotFoundException;
	}
}


function testGetMapper(Registry $registry): void
{
	// Default mapper (no argument)
	assertType('Nette\Assets\FilesystemMapper', $registry->getMapper());

	// Explicit 'default'
	assertType('Nette\Assets\FilesystemMapper', $registry->getMapper('default'));

	// Named mapper
	assertType('Nette\Assets\ViteMapper', $registry->getMapper('vite'));

	// Another named mapper
	assertType('Nette\Assets\FilesystemMapper', $registry->getMapper('images'));

	// Custom mapper class (FQCN in config)
	assertType('CustomMapper', $registry->getMapper('custom'));

	// Unknown mapper -> falls back to declared return type
	assertType('Nette\Assets\Mapper', $registry->getMapper('unknown'));
}
