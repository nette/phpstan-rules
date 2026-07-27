<?php declare(strict_types=1);

use Nette\Utils\Strings;
use function PHPStan\Testing\assertType;


// match() — exact shape from capture groups
assertType('array{non-falsy-string, numeric-string, non-empty-string}|null', Strings::match('subject', '#(\d+)-(\w+)#'));

// match() — no capture groups (group 0 only)
assertType('array{non-falsy-string}|null', Strings::match('subject', '#plain#'));

// match() — named capture group
assertType('array{0: non-falsy-string, year: non-falsy-string&numeric-string, 1: non-falsy-string&numeric-string}|null', Strings::match('subject', '#(?<year>\d{4})#'));

// match() — optional capture group
assertType('array{0: string, 1?: numeric-string}|null', Strings::match('subject', '#(\d+)?#'));

// match() — captureOffset adds offsets to every group
assertType('array{array{non-empty-string, int<-1, max>}, array{numeric-string, int<-1, max>}}|null', Strings::match('subject', '#(\d+)#', captureOffset: true));

// match() — unmatchedAsNull
assertType('array{non-empty-string, numeric-string}|null', Strings::match('subject', '#(\d+)#', unmatchedAsNull: true));


// matchAll() — exact shape, default PREG_SET_ORDER
assertType('list<array{string, numeric-string}>', Strings::matchAll('subject', '#(\d+)#'));

// matchAll() — patternOrder (PREG_PATTERN_ORDER)
assertType('array{list<string>, list<numeric-string>}', Strings::matchAll('subject', '#(\d+)#', patternOrder: true));

// matchAll() — captureOffset
assertType('list<array{array{string, int<-1, max>}, array{numeric-string, int<-1, max>}}>', Strings::matchAll('subject', '#(\d+)#', captureOffset: true));


// matchAll() — lazy falls back to a generic Generator shape
assertType('Generator<int, array<string>, mixed, mixed>', Strings::matchAll('subject', '#(\d+)#', lazy: true));

// non-constant pattern falls back to a generic shape
function dynamicPattern(string $s, string $pattern): void
{
	assertType('array<string>|null', Strings::match($s, $pattern));
}


// split() is unaffected by the regex shape matcher
assertType('list<string>', Strings::split('subject', '#\s+#'));

// split() — captureOffset
assertType('list<array{string, int<0, max>}>', Strings::split('subject', '#\s+#', captureOffset: true));
