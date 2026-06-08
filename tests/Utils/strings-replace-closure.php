<?php declare(strict_types=1);

use Nette\Utils\Strings;
use function PHPStan\Testing\assertType;


// $matches shape derived from capture groups
Strings::replace('subject', '#(\d+)-(\w+)#', function (array $matches): string {
	assertType('array{non-falsy-string, decimal-int-string, non-empty-string}', $matches);
	return '';
});

// no capture groups — group 0 only
Strings::replace('subject', '#plain#', function (array $matches): string {
	assertType('array{non-falsy-string}', $matches);
	return '';
});

// captureOffset adds offsets to every group
Strings::replace('subject', '#(\d+)#', function (array $matches): string {
	assertType('array{array{non-empty-string, int<-1, max>}, array{decimal-int-string, int<-1, max>}}', $matches);
	return '';
}, captureOffset: true);
