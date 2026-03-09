<?php declare(strict_types=1);

use Nette\Assets\FilesystemMapper;
use Nette\Assets\ViteMapper;
use function PHPStan\Testing\assertType;


function testFilesystemMapperGetAsset(FilesystemMapper $mapper): void
{
	// Image assets
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('photo.jpg'));
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('logo.png'));
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('icon.svg'));
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('banner.webp'));

	// Script assets
	assertType('Nette\Assets\ScriptAsset', $mapper->getAsset('app.js'));
	assertType('Nette\Assets\ScriptAsset', $mapper->getAsset('module.mjs'));

	// Style assets
	assertType('Nette\Assets\StyleAsset', $mapper->getAsset('style.css'));

	// Audio assets
	assertType('Nette\Assets\AudioAsset', $mapper->getAsset('song.mp3'));

	// Video assets
	assertType('Nette\Assets\VideoAsset', $mapper->getAsset('clip.mp4'));

	// Font assets
	assertType('Nette\Assets\FontAsset', $mapper->getAsset('font.woff2'));

	// Subdirectory reference
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('images/photo.jpg'));

	// Unknown extension -> falls back to declared return type
	assertType('Nette\Assets\Asset', $mapper->getAsset('data.xml'));
}


function testViteMapperGetAsset(ViteMapper $mapper): void
{
	// Image assets
	assertType('Nette\Assets\ImageAsset', $mapper->getAsset('photo.jpg'));

	// Script assets (safe: EntryAsset extends ScriptAsset)
	assertType('Nette\Assets\ScriptAsset', $mapper->getAsset('app.js'));

	// Style assets
	assertType('Nette\Assets\StyleAsset', $mapper->getAsset('style.css'));

	// Unknown extension -> falls back to declared return type
	assertType('Nette\Assets\Asset', $mapper->getAsset('data.xml'));
}
