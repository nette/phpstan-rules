<?php declare(strict_types=1);

use Nette\Utils\Strings;
use function PHPStan\Testing\assertType;


// match() — defaults
assertType('array<string>|null', Strings::match('subject', '#pattern#'));

// match() — captureOffset
assertType('array<array{string, int<0, max>}>|null', Strings::match('subject', '#pattern#', captureOffset: true));

// match() — unmatchedAsNull
assertType('array<string|null>|null', Strings::match('subject', '#pattern#', unmatchedAsNull: true));

// match() — captureOffset + unmatchedAsNull
assertType('array<array{string|null, int<0, max>}>|null', Strings::match('subject', '#pattern#', captureOffset: true, unmatchedAsNull: true));


// matchAll() — defaults (PREG_SET_ORDER)
assertType('list<array<string>>', Strings::matchAll('subject', '#pattern#'));

// matchAll() — captureOffset
assertType('list<array<array{string, int<0, max>}>>', Strings::matchAll('subject', '#pattern#', captureOffset: true));

// matchAll() — unmatchedAsNull
assertType('list<array<string|null>>', Strings::matchAll('subject', '#pattern#', unmatchedAsNull: true));

// matchAll() — captureOffset + unmatchedAsNull
assertType('list<array<array{string|null, int<0, max>}>>', Strings::matchAll('subject', '#pattern#', captureOffset: true, unmatchedAsNull: true));

// matchAll() — lazy
assertType('Generator<int, array<string>, mixed, mixed>', Strings::matchAll('subject', '#pattern#', lazy: true));

// matchAll() — lazy + captureOffset
assertType('Generator<int, array<array{string, int<0, max>}>, mixed, mixed>', Strings::matchAll('subject', '#pattern#', captureOffset: true, lazy: true));

// matchAll() — lazy + unmatchedAsNull
assertType('Generator<int, array<string|null>, mixed, mixed>', Strings::matchAll('subject', '#pattern#', unmatchedAsNull: true, lazy: true));

// matchAll() — patternOrder
assertType('array<list<string>>', Strings::matchAll('subject', '#pattern#', patternOrder: true));

// matchAll() — patternOrder + captureOffset
assertType('array<list<array{string, int<0, max>}>>', Strings::matchAll('subject', '#pattern#', captureOffset: true, patternOrder: true));


// split() — defaults
assertType('list<string>', Strings::split('subject', '#pattern#'));

// split() — captureOffset
assertType('list<array{string, int<0, max>}>', Strings::split('subject', '#pattern#', captureOffset: true));
