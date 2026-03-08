<?php declare(strict_types=1);

use Nette\Database\Table\ActiveRow;
use function PHPStan\Testing\assertType;


class RefEventRow extends ActiveRow
{
}


function testRef(ActiveRow $row): void
{
	// Simple table name
	assertType('RefEventRow|null', $row->ref('ref_event'));

	// With throughColumn
	assertType('RefEventRow|null', $row->ref('ref_event', 'event_id'));

	// Unknown table -> falls back to declared return type
	assertType('Nette\Database\Table\ActiveRow|null', $row->ref('unknown'));
}
