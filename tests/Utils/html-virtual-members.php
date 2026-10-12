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
assertType(Html::class, $html->class('foo'));
assertType(Html::class, $html->checked(true));
assertType(Html::class, $html->cols(80));
assertType(Html::class, $html->high(1.0));

// @method — with second argument (appendAttribute)
assertType(Html::class, $html->class('foo', true));

// __call — getXxx(), setXxx(), addXxx()
assertType('mixed', $html->getClass());
assertType(Html::class, $html->setClass('foo'));
assertType(Html::class, $html->addClass('bar'));

// real methods — return static
assertType(Html::class, $html->href('/path'));
assertType(Html::class, $html->data('key', 'value'));
