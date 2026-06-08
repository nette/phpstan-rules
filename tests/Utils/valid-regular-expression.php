<?php declare(strict_types=1);

namespace Tests\Utils;

use Nette\Utils\Strings;


function test(): void
{
	// valid patterns — no error
	$a = Strings::match('foo', '#valid\d+#');
	$b = Strings::matchAll('foo', '~[a-z]+~i');
	$c = Strings::split('foo', '#\s+#');
	$d = Strings::replace('foo', ['#good#' => 'x']);

	// valid patterns via named args (reordered) — no error, and the subject
	// must NOT be mistaken for the pattern
	$na = Strings::match(pattern: '#valid#', subject: 'foo');
	$nb = Strings::match(subject: 'foo', pattern: '#valid#');

	// invalid patterns — one error each
	$e = Strings::match('foo', '#invalid(#');
	$f = Strings::split('foo', '#bad[#');
	$g = Strings::replace('foo', ['#good#' => 'x', '#wrong(#' => 'y']);
	$i = Strings::matchAll('foo', '#bad)#');
	$j = Strings::match(subject: 'foo', pattern: '#named(bad#');

	// non-constant pattern — skipped
	$pattern = '#' . strtoupper('x') . '#';
	$h = Strings::match('foo', $pattern);
}
