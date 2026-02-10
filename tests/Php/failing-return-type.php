<?php declare(strict_types=1);

use function PHPStan\Testing\assertType;


// === Functions — trivial false ===

// System information
assertType('non-empty-string', getcwd());
assertType('false', gethostname() === false);
assertType('false', getlastmod() === false);
assertType('false', getmygid() === false);
assertType('false', getmyinode() === false);
assertType('false', getmypid() === false);
assertType('false', getmyuid() === false);
assertType('false', getrusage() === false);

// Output buffering (false = no active buffer)
assertType('false', ob_get_clean() === false);
assertType('false', ob_get_contents() === false);
assertType('false', ob_get_flush() === false);
assertType('false', ob_get_length() === false);

// Compression
assertType('false', deflate_init(ZLIB_ENCODING_GZIP) === false);
assertType('false', gzcompress('data') === false);
assertType('false', gzencode('data') === false);
assertType('false', gzdeflate('data') === false);
assertType('false', inflate_init(ZLIB_ENCODING_GZIP) === false);
assertType('false', zlib_encode('data', ZLIB_ENCODING_GZIP) === false);

// JSON
assertType('non-empty-string', json_encode('data'));

// Regex (constant pattern — |false stripped)
function testRegexConstant(string $s): void
{
	assertType('false', preg_match('/a/', $s) === false);
	assertType('false', preg_split('/a/', $s) === false);
	assertType('false', preg_grep('/a/', [$s]) === false);
}


// File operations (need file handle)
function testFileOps(): void
{
	$f = fopen('php://memory', 'r+');
	\assert($f !== false);
	assertType('false', fstat($f) === false);
	assertType('false', ftell($f) === false);
}


// Date/Time
function testDateTimezoneGet(DateTime $dt): void
{
	assertType('DateTimeZone', date_timezone_get($dt));
}


// Intl
assertType('false', grapheme_strlen('foo') === false);
assertType('false', normalizer_normalize('foo') === false);


function testMsgfmtFormat(MessageFormatter $mf): void
{
	assertType('false', msgfmt_format($mf, [1]) === false);
}


// Session
assertType('false', session_id() === false);
assertType('non-falsy-string', session_name());

// === Functions — PHPStan reports |false but current PHP no longer returns false ===

assertType('false', hash('sha256', 'data') === false);
assertType('false', hash_hmac('sha256', 'data', 'key') === false);
assertType('false', hash_hkdf('sha256', 'key') === false);
assertType('false', hash_pbkdf2('sha256', 'password', 'salt', 1000) === false);


function testArrayCombine(string $s): void
{
	assertType('false', array_combine([$s], [$s]) === false);
}


function testStringFunctions(string $s): void
{
	assertType('false', explode(',', $s) === false);
	assertType('false', str_split($s) === false);
	assertType('false', mb_strlen($s) === false);
}


function testMbEncodingAliases(string $s): void
{
	assertType('false', mb_encoding_aliases($s) === false);
}


function testDateMethods(DateTime $dt, DateInterval $di): void
{
	assertType('DateTime', date_add($dt, $di));
	assertType('DateTime', date_sub($dt, $di));
	assertType('DateTime', date_date_set($dt, 2024, 1, 1));
	assertType('DateTime', date_isodate_set($dt, 2024, 1));
	assertType('DateTime', date_time_set($dt, 12, 0));
	assertType('DateTime', date_timestamp_set($dt, 1_000_000));
	assertType('DateTime', date_timezone_set($dt, new DateTimeZone('UTC')));
}


assertType('false', openssl_random_pseudo_bytes(16) === false);
assertType('false', substr_compare('a', 'b', 0) === false);
assertType('false', sleep(0) === false);
assertType('false', long2ip(0) === false);


// === Instance methods ===

// Intl — Collator
function testCollator(Collator $c): void
{
	assertType('false', $c->compare('a', 'b') === false);
}


// Intl — IntlDateFormatter
function testIntlDateFormatter(IntlDateFormatter $f): void
{
	assertType('string', $f->format(0));
	assertType('false', $f->getCalendar() === false);
	assertType('false', $f->getDateType() === false);
	assertType('false', $f->getLocale() === false);
	assertType('false', $f->getPattern() === false);
	assertType('false', $f->getTimeType() === false);
	assertType('false', $f->getTimeZoneId() === false);
}


// Intl — NumberFormatter
function testNumberFormatter(NumberFormatter $nf): void
{
	assertType('string', $nf->format(42));
	assertType('string', $nf->formatCurrency(42.0, 'USD'));
}


// Intl — MessageFormatter
function testMessageFormatter(MessageFormatter $mf): void
{
	assertType('false', msgfmt_format($mf, [1]) === false);
	assertType('false', $mf->format([1]) === false);
}


// Intl — Transliterator
function testTransliterator(Transliterator $t): void
{
	assertType('string', $t->transliterate('foo'));
}


// Intl — static methods and other
assertType('false', IntlDateFormatter::formatObject(new DateTime, 'yyyy-MM-dd') === false);
assertType('false', MessageFormatter::formatMessage('en', '{0}', [1]) === false);
assertType('false', Transliterator::listIDs() === false);
assertType('false', ResourceBundle::getLocales('') === false);


function testIntlDatePatternGenerator(IntlDatePatternGenerator $g): void
{
	assertType('false', $g->getBestPattern('yyyyMMdd') === false);
}


// DOM
function testDomDocument(DOMDocument $doc): void
{
	assertType('false', $doc->adoptNode($doc->createElement('x')) === false);
	assertType('false', $doc->createAttribute('name') === false);
	assertType('false', $doc->createAttributeNS('http://example.com', 'ns:name') === false);
	assertType('false', $doc->createCDATASection('data') === false);
	assertType('DOMElement', $doc->createElement('div'));
	assertType('false', $doc->createElementNS('http://example.com', 'ns:div') === false);
	assertType('false', $doc->createEntityReference('amp') === false);
	assertType('false', $doc->createProcessingInstruction('xml', 'version="1.0"') === false);
	assertType('false', $doc->saveHTML() === false);
	assertType('false', $doc->saveXML() === false);
}


function testDomNode(DOMNode $parent, DOMNode $child, DOMNode $newChild): void
{
	assertType('false', $parent->appendChild($child) === false);
	assertType('false', $parent->insertBefore($child) === false);
	assertType('false', $parent->removeChild($child) === false);
	assertType('false', $parent->replaceChild($newChild, $child) === false);
}


function testDomText(DOMText $text): void
{
	assertType('DOMText', $text->splitText(5));
}


// PDO
function testPdoStatement(PDOStatement $stmt): void
{
	assertType('array', $stmt->fetchAll());
}


// === Static methods ===

assertType('string', Normalizer::normalize('foo'));
assertType('DateInterval', DateInterval::createFromDateString('1 day'));
