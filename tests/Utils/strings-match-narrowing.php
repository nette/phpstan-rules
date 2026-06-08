<?php declare(strict_types=1);

use Nette\Utils\Strings;
use function PHPStan\Testing\assertType;


function test(string $s): void
{
	if (Strings::match($s, '#\d+#')) {
		assertType('non-empty-string', $s);
	}

	// outside the condition the subject is unchanged
	assertType('string', $s);

	if (Strings::match($s, '#^foo$#')) {
		assertType('non-falsy-string', $s);
	}

	// matchAll narrows the subject too (non-empty result is truthy)
	if (Strings::matchAll($s, '#[a-z]+#')) {
		assertType('non-empty-string', $s);
	}

	// a pattern that can match an empty string does not narrow
	if (Strings::match($s, '#.*#')) {
		assertType('string', $s);
	}
}
