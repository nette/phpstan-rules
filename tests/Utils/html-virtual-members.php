<?php declare(strict_types=1);

use Nette\Utils\Html;
use function PHPStan\Testing\assertType;

$html = Html::el('div');

// @property — string, bool, int, float
assertType('string|null', $html->class);
assertType('bool|null', $html->checked);
assertType('int|null', $html->cols);
assertType('float|null', $html->high);
assertType('string|null', $html->id);

// universalObjectCratesClasses — undeclared property
assertType('mixed', $html->nonExistent);

// @method — fluent setters
assertType('Nette\Utils\Html', $html->class('foo'));
assertType('Nette\Utils\Html', $html->checked(true));
assertType('Nette\Utils\Html', $html->cols(80));
assertType('Nette\Utils\Html', $html->high(1.0));

// @method — with second argument (appendAttribute)
assertType('Nette\Utils\Html', $html->class('foo', true));

// __call — getXxx(), setXxx(), addXxx()
assertType('mixed', $html->getClass());
assertType('Nette\Utils\Html', $html->setClass('foo'));
assertType('Nette\Utils\Html', $html->addClass('bar'));

// real methods — return static
assertType('Nette\Utils\Html', $html->href('/path'));
assertType('Nette\Utils\Html', $html->data('key', 'value'));
