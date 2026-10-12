<?php declare(strict_types=1);

use Nette\Assets\Registry;
use function PHPStan\Testing\assertType;


function testRegistryGetAsset(Registry $registry): void
{
	// Default mapper (FilesystemMapper) — image
	assertType(Nette\Assets\ImageAsset::class, $registry->getAsset('photo.jpg'));
	assertType(Nette\Assets\ImageAsset::class, $registry->getAsset('logo.png'));

	// Default mapper — script
	assertType(Nette\Assets\ScriptAsset::class, $registry->getAsset('app.js'));

	// Default mapper — style
	assertType(Nette\Assets\StyleAsset::class, $registry->getAsset('style.css'));

	// Default mapper — audio
	assertType(Nette\Assets\AudioAsset::class, $registry->getAsset('song.mp3'));

	// Default mapper — video
	assertType(Nette\Assets\VideoAsset::class, $registry->getAsset('clip.mp4'));

	// Default mapper — font
	assertType(Nette\Assets\FontAsset::class, $registry->getAsset('font.woff2'));

	// Explicit FilesystemMapper via prefix
	assertType(Nette\Assets\ImageAsset::class, $registry->getAsset('images:photo.jpg'));

	// ViteMapper via prefix — also narrows
	assertType(Nette\Assets\ScriptAsset::class, $registry->getAsset('vite:app.js'));
	assertType(Nette\Assets\StyleAsset::class, $registry->getAsset('vite:style.css'));

	// Custom mapper (FQCN) -> no narrowing
	assertType(Nette\Assets\Asset::class, $registry->getAsset('custom:photo.jpg'));

	// Unknown mapper -> falls back to declared return type
	assertType(Nette\Assets\Asset::class, $registry->getAsset('cdn:photo.jpg'));

	// Unknown extension -> falls back to declared return type
	assertType(Nette\Assets\Asset::class, $registry->getAsset('data.xml'));
}


function testRegistryTryGetAsset(Registry $registry): void
{
	// tryGetAsset adds |null
	assertType('Nette\Assets\ImageAsset|null', $registry->tryGetAsset('photo.jpg'));
	assertType('Nette\Assets\ScriptAsset|null', $registry->tryGetAsset('app.js'));

	// ViteMapper via prefix
	assertType('Nette\Assets\StyleAsset|null', $registry->tryGetAsset('vite:style.css'));

	// Unknown mapper -> falls back
	assertType('Nette\Assets\Asset|null', $registry->tryGetAsset('cdn:photo.jpg'));

	// Unknown extension -> falls back
	assertType('Nette\Assets\Asset|null', $registry->tryGetAsset('data.xml'));
}
