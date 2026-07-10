<?php declare(strict_types=1);

namespace InterfacePropertyTagClean;

/**
 * @property-read string $url
 */
interface Asset
{
}

class PlainAsset implements Asset
{
}


function readViaInterface(Asset $asset): string
{
	return $asset->url;
}


function readViaClass(PlainAsset $asset): string
{
	return $asset->url;
}


function readNullsafe(?Asset $asset): ?string
{
	return $asset?->url;
}
