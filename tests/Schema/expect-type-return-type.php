<?php declare(strict_types=1);

use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use function PHPStan\Testing\assertType;

// A constant expression is resolved to the element it builds; up to nette/schema 1.3
// every expression is a plain Type, later versions have kind-specific subclasses.
//
// BEWARE: while the dev dependency is nette/schema 1.3, Type is also the declared return
// type, so these assertions pass with the extension disabled and prove nothing but that
// it does not crash. Once 1.4 is out, raise the dependency and assert StringType for
// 'string', NumberType for 'int' and 'int|float', ArrayType for 'list', AnyOf for
// 'int|string' - that is what the extension is actually for.
assertType(Type::class, Expect::type('string'));
assertType(Type::class, Expect::type('int'));
assertType(Type::class, Expect::type(DateTime::class));


// A non-constant expression keeps the declared type
function nonConstant(string $expr): void
{
	assertType(Type::class, Expect::type($expr));
}
