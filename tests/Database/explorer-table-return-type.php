<?php declare(strict_types=1);

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use function PHPStan\Testing\assertType;


class BookingRow extends ActiveRow
{
}

class EventVideoRow extends ActiveRow
{
}

class CustomEntity extends ActiveRow
{
}


function testExplorerTable(Explorer $explorer): void
{
	// Convention-based resolution
	assertType(
		'Nette\Database\Table\Selection<BookingRow>',
		$explorer->table('booking'),
	);

	// Multi-word table name (snake_case -> PascalCase)
	assertType(
		'Nette\Database\Table\Selection<EventVideoRow>',
		$explorer->table('event_video'),
	);

	// Explicit table override
	assertType(
		'Nette\Database\Table\Selection<CustomEntity>',
		$explorer->table('custom_table'),
	);

	// Unknown table -> falls back to declared return type
	assertType(
		'Nette\Database\Table\Selection<Nette\Database\Table\ActiveRow>',
		$explorer->table('unknown_table'),
	);
}
