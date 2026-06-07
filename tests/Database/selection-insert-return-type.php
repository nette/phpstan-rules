<?php declare(strict_types=1);

use Nette\Database\Explorer;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use function PHPStan\Testing\assertType;


class InsertBookingRow extends ActiveRow
{
}


/**
 * @param array<string, mixed> $data
 * @param array<int, array<string, mixed>> $rows
 */
function testInsert(Explorer $explorer, array $data, array $rows, Selection $other): void
{
	$bookings = $explorer->table('insert_booking'); // Selection<InsertBookingRow>

	// Constant array with string keys -> single row insert into a mapped table -> concrete EntityRow
	assertType('InsertBookingRow', $bookings->insert(['name' => 'foo', 'date' => 'bar']));

	// Non-constant string-keyed array -> single row insert -> concrete EntityRow
	assertType('InsertBookingRow', $bookings->insert($data));

	// List of rows (int keys) -> multi-row insert -> declared type kept (not a mapped single row)
	assertType('int', $bookings->insert($rows));

	// Selection argument (object, not array) -> multi-row insert -> declared type kept
	assertType('int', $bookings->insert($other));
}


/** Bare ActiveRow (unmapped table) keeps the honest union — narrowing applies only to mapped rows. */
function testInsertUnmapped(Explorer $explorer): void
{
	$unknown = $explorer->table('unknown_table'); // Selection<ActiveRow>

	assertType(
		'array<string, mixed>|Nette\Database\Table\ActiveRow',
		$unknown->insert(['name' => 'foo']),
	);
}
