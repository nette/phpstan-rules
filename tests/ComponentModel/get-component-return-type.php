<?php declare(strict_types=1);

use Nette\ComponentModel\Container;
use function PHPStan\Testing\assertType;


class PollControl extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;
}

class CalendarControl extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;
}

class TestPresenter extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	protected function createComponentPoll(): PollControl
	{
		return new PollControl;
	}


	protected function createComponentCalendar(): CalendarControl
	{
		return new CalendarControl;
	}


	public function test(): void
	{
		assertType('PollControl', $this->getComponent('poll'));
		assertType('CalendarControl', $this->getComponent('calendar'));
		assertType('PollControl', $this['poll']);
		assertType('CalendarControl', $this['calendar']);

		// $throw = false → nullable
		assertType('PollControl|null', $this->getComponent('poll', false));
		assertType('CalendarControl|null', $this->getComponent('calendar', false));
	}
}


// no factory method → falls back to declared return type
class EmptyPresenter extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	public function test(): void
	{
		assertType('Nette\ComponentModel\IComponent', $this->getComponent('unknown'));
		assertType('Nette\ComponentModel\IComponent', $this['unknown']);

		// $throw = false → nullable (falls back to declared conditional return type)
		assertType('Nette\ComponentModel\IComponent|null', $this->getComponent('unknown', false));
	}
}
