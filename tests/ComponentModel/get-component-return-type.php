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


// nested component access: chained ($this['a']['b']) and dash ($this['a-b'])
class WidgetControl extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;
}

class PanelControl extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	protected function createComponentWidget(): WidgetControl
	{
		return new WidgetControl;
	}
}

class NestedPresenter extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	protected function createComponentPanel(): PanelControl
	{
		return new PanelControl;
	}


	public function test(): void
	{
		// chained access without an intermediate variable
		assertType('WidgetControl', $this['panel']['widget']);
		assertType('WidgetControl', $this->getComponent('panel')['widget']);

		// dash notation
		assertType('WidgetControl', $this['panel-widget']);

		// $throw = false at the end of a dash path
		assertType('WidgetControl|null', $this->getComponent('panel-widget', false));

		// unknown segment → declared type kept
		assertType('Nette\ComponentModel\IComponent', $this['panel-unknown']);
		assertType('Nette\ComponentModel\IComponent', $this['unknown-widget']);
	}
}


// regression: a non-form control whose factory calls an add*() method must NOT be
// resolved as a form control, even when the method name collides with Nette\Forms\Container
class GridControl extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	public function addText(string $name): self
	{
		return $this;
	}
}

class GridPresenter extends Container implements \ArrayAccess
{
	use \Nette\ComponentModel\ArrayAccess;

	protected function createComponentGrid(): GridControl
	{
		$grid = new GridControl;
		$grid->addText('name');
		return $grid;
	}


	public function test(): void
	{
		// must stay the declared type, not become Nette\Forms\Controls\TextInput
		assertType('Nette\ComponentModel\IComponent', $this['grid']['name']);
		assertType('Nette\ComponentModel\IComponent', $this['grid-name']);
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
