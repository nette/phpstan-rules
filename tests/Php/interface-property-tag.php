<?php declare(strict_types=1);

namespace InterfacePropertyTag;

use function PHPStan\Testing\assertType;


/**
 * @property-read string $url
 * @property-read ?string $file
 */
interface Asset
{
	public function __toString(): string;
}

interface RenderableAsset extends Asset
{
}

class PlainAsset implements Asset
{
	public function __toString(): string
	{
		return '';
	}
}

class NativeAsset implements Asset
{
	public function __construct(
		public readonly int $url, // native property beats the tag
	) {
	}


	public function __toString(): string
	{
		return '';
	}
}


function testInterface(Asset $asset): void
{
	assertType('string', $asset->url);
	assertType('string|null', $asset->file);
}


function testNullsafe(?Asset $asset): void
{
	assertType('string|null', $asset?->url);
}


function testChildInterface(RenderableAsset $asset): void
{
	assertType('string', $asset->url);
}


function testImplementingClass(PlainAsset $asset): void
{
	assertType('string', $asset->url);
}


function testNativePropertyWins(NativeAsset $asset): void
{
	assertType('int', $asset->url);
}


function testNarrowingWins(Asset $asset): void
{
	if ($asset->file !== null) {
		assertType('string', $asset->file);
	}
}
