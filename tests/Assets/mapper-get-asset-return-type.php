<?php declare(strict_types=1);

use Nette\Assets\{FilesystemMapper, ViteMapper};
use function PHPStan\Testing\assertType;


function testFilesystemMapperGetAsset(FilesystemMapper $mapper): void
{
	// Image assets
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('photo.jpg'));
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('logo.png'));
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('icon.svg'));
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('banner.webp'));

	// Script assets
	assertType(Nette\Assets\ScriptAsset::class, $mapper->getAsset('app.js'));
	assertType(Nette\Assets\ScriptAsset::class, $mapper->getAsset('module.mjs'));

	// Style assets
	assertType(Nette\Assets\StyleAsset::class, $mapper->getAsset('style.css'));

	// Audio assets
	assertType(Nette\Assets\AudioAsset::class, $mapper->getAsset('song.mp3'));

	// Video assets
	assertType(Nette\Assets\VideoAsset::class, $mapper->getAsset('clip.mp4'));

	// Font assets
	assertType(Nette\Assets\FontAsset::class, $mapper->getAsset('font.woff2'));

	// Subdirectory reference
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('images/photo.jpg'));

	// Unknown extension -> falls back to declared return type
	assertType(Nette\Assets\Asset::class, $mapper->getAsset('data.xml'));
}


function testViteMapperGetAsset(ViteMapper $mapper): void
{
	// Image assets
	assertType(Nette\Assets\ImageAsset::class, $mapper->getAsset('photo.jpg'));

	// Script assets (safe: EntryAsset extends ScriptAsset)
	assertType(Nette\Assets\ScriptAsset::class, $mapper->getAsset('app.js'));

	// Style assets
	assertType(Nette\Assets\StyleAsset::class, $mapper->getAsset('style.css'));

	// Unknown extension -> falls back to declared return type
	assertType(Nette\Assets\Asset::class, $mapper->getAsset('data.xml'));
}
