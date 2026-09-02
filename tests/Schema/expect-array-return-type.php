<?php declare(strict_types=1);

use Nette\Schema\Elements\Structure;
use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use function PHPStan\Testing\assertType;

// The plain array element is whatever Expect::array() builds without a shape: a Type up to
// nette/schema 1.3, an ArrayType later. BEWARE: with the 1.3 dev dependency Type is also the
// declared return type, so only the Structure assertions below can fail. Once 1.4 is out,
// raise the dependency and assert ArrayType for the four plain cases.

// No argument → Type
assertType(Type::class, Expect::array());

// Empty array → Type
assertType(Type::class, Expect::array([]));

// Non-Schema values → Type
assertType(Type::class, Expect::array(['key1' => 'val1', 'val3']));

// Schema values (shape definition) → Structure
assertType(Structure::class, Expect::array(['a' => Expect::string()]));
assertType(Structure::class, Expect::array([Expect::int(), Expect::string()]));

// Null argument → Type
assertType(Type::class, Expect::array(null));
