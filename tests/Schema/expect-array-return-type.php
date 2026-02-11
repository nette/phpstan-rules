<?php declare(strict_types=1);

use Nette\Schema\Elements\Structure;
use Nette\Schema\Elements\Type;
use Nette\Schema\Expect;
use function PHPStan\Testing\assertType;


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
