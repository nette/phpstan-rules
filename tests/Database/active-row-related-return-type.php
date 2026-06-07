<?php declare(strict_types=1);

use Nette\Database\Table\ActiveRow;
use function PHPStan\Testing\assertType;


class RelBookingRow extends ActiveRow
{
}
class RelEventRow extends ActiveRow
{
}


function testRelated(ActiveRow $row): void
{
	// Simple table name
	assertType(
		'Nette\Database\Table\GroupedSelection<RelBookingRow>',
		$row->related('rel_booking'),
	);

	// table.column format
	assertType(
		'Nette\Database\Table\GroupedSelection<RelEventRow>',
		$row->related('rel_event.course_id'),
	);

	// Unknown table -> falls back to declared return type
	assertType(
		'Nette\Database\Table\GroupedSelection<Nette\Database\Table\ActiveRow>',
		$row->related('unknown'),
	);
}
