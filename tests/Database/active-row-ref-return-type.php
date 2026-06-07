<?php declare(strict_types=1);

use Nette\Database\Table\ActiveRow;
use function PHPStan\Testing\assertType;


class RefEventRow extends ActiveRow
{
}


/**
 * @property-read int $eventId
 * @property-read int|null $optionalEventId
 */
class RefOrderRow extends ActiveRow
{
}


/**
 * Project without camelCase convention: properties match raw column names.
 * @property-read int $event_id
 * @property-read mixed $untyped_id
 */
class RefRawRow extends ActiveRow
{
}


function testRef(ActiveRow $row): void
{
	// Generic ActiveRow has no column type info -> stays nullable (safe default)
	assertType('RefEventRow|null', $row->ref('ref_event'));

	// With throughColumn, but caller column nullability is unknown -> nullable
	assertType('RefEventRow|null', $row->ref('ref_event', 'event_id'));

	// Unknown table -> falls back to declared return type
	assertType('Nette\Database\Table\ActiveRow|null', $row->ref('unknown'));
}


function testRefNullability(RefOrderRow $order): void
{
	// Non-nullable FK column -> referenced row is guaranteed to exist
	assertType('RefEventRow', $order->ref('ref_event', 'event_id'));

	// Nullable FK column -> referenced row may be null
	assertType('RefEventRow|null', $order->ref('ref_event', 'optional_event_id'));

	// Unknown column on caller -> nullable (safe default)
	assertType('RefEventRow|null', $order->ref('ref_event', 'missing_column'));
}


function testRefRawColumnNames(RefRawRow $row): void
{
	// No camelCase convention: non-nullable property matches the raw column name
	assertType('RefEventRow', $row->ref('ref_event', 'event_id'));

	// Property without a concrete type (mixed) -> stays nullable (safe default)
	assertType('RefEventRow|null', $row->ref('ref_event', 'untyped_id'));
}
