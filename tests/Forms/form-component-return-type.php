<?php declare(strict_types=1);

use Nette\Forms\Form;
use function PHPStan\Testing\assertType;


function testAddText(): void
{
	$form = new Form;
	$form->addText('name', 'Name:');
	$form->addPassword('password', 'Password:');
	assertType(Nette\Forms\Controls\TextInput::class, $form['name']);
	assertType(Nette\Forms\Controls\TextInput::class, $form->getComponent('name'));
	assertType(Nette\Forms\Controls\TextInput::class, $form['password']);

	// $throw = false → nullable
	assertType('Nette\Forms\Controls\TextInput|null', $form->getComponent('name', false));
}


function testAddSelect(): void
{
	$form = new Form;
	$form->addSelect('country', 'Country:', ['CZ' => 'Czech Republic']);
	$form->addMultiSelect('tags', 'Tags:', ['a' => 'A', 'b' => 'B']);
	assertType(Nette\Forms\Controls\SelectBox::class, $form['country']);
	assertType(Nette\Forms\Controls\MultiSelectBox::class, $form['tags']);
}


function testAddCheckbox(): void
{
	$form = new Form;
	$form->addCheckbox('agree', 'I agree');
	$form->addCheckboxList('colors', 'Colors:', ['r' => 'Red']);
	$form->addRadioList('gender', 'Gender:', ['m' => 'Male']);
	assertType(Nette\Forms\Controls\Checkbox::class, $form['agree']);
	assertType(Nette\Forms\Controls\CheckboxList::class, $form['colors']);
	assertType(Nette\Forms\Controls\RadioList::class, $form['gender']);
}


function testAddOther(): void
{
	$form = new Form;
	$form->addTextArea('bio', 'Bio:');
	$form->addEmail('email', 'Email:');
	$form->addInteger('age', 'Age:');
	$form->addHidden('token');
	$form->addSubmit('send', 'Send');
	$form->addButton('cancel', 'Cancel');
	$form->addUpload('avatar', 'Avatar:');
	assertType(Nette\Forms\Controls\TextArea::class, $form['bio']);
	assertType(Nette\Forms\Controls\TextInput::class, $form['email']);
	assertType(Nette\Forms\Controls\TextInput::class, $form['age']);
	assertType(Nette\Forms\Controls\HiddenField::class, $form['token']);
	assertType(Nette\Forms\Controls\SubmitButton::class, $form['send']);
	assertType(Nette\Forms\Controls\Button::class, $form['cancel']);
	assertType(Nette\Forms\Controls\UploadControl::class, $form['avatar']);
}


function testAddContainer(): void
{
	$form = new Form;
	$form->addContainer('address');
	assertType(Nette\Forms\Container::class, $form['address']);
}


function testUnknownComponent(): void
{
	$form = new Form;
	assertType(Nette\Forms\Controls\BaseControl::class, $form['unknown']);
}


function testFallbackFromOtherMethod(Form $form): void
{
	assertType(Nette\Forms\Controls\BaseControl::class, $form['unknown']);
}


// Cross-method: form created in createComponentXxx(), accessed in another method
class CrossMethodPresenter extends Nette\ComponentModel\Container implements ArrayAccess
{
	use Nette\ComponentModel\ArrayAccess;

	public function createComponentMyForm(): Form
	{
		$form = new Form;
		$form->addText('name');
		$form->addSelect('country', 'Country:', []);
		$form->addSubmit('send');
		return $form;
	}


	public function test(): void
	{
		$form = $this['myForm'];
		assertType(Nette\Forms\Controls\TextInput::class, $form['name']);
		assertType(Nette\Forms\Controls\SelectBox::class, $form['country']);
		assertType(Nette\Forms\Controls\SubmitButton::class, $form['send']);

		// chained access without an intermediate variable
		assertType(Nette\Forms\Controls\TextInput::class, $this['myForm']['name']);
		assertType(Nette\Forms\Controls\SelectBox::class, $this->getComponent('myForm')['country']);

		// dash notation
		assertType(Nette\Forms\Controls\TextInput::class, $this['myForm-name']);
		assertType(Nette\Forms\Controls\SubmitButton::class, $this['myForm-send']);

		// $throw = false at the end of a dash path
		assertType('Nette\Forms\Controls\TextInput|null', $this->getComponent('myForm-name', false));

		// unknown field: chained resolves the form (BaseControl), dash falls back to the
		// presenter's declared type (IComponent) - both safe, never a wrong type
		assertType(Nette\Forms\Controls\BaseControl::class, $this['myForm']['unknown']);
		assertType(Nette\ComponentModel\IComponent::class, $this['myForm-unknown']);
	}
}


// Sub-containers (addContainer) are out of scope: graceful fallback, never a wrong type
class SubContainerPresenter extends Nette\ComponentModel\Container implements ArrayAccess
{
	use Nette\ComponentModel\ArrayAccess;

	public function createComponentMyForm(): Form
	{
		$form = new Form;
		$address = $form->addContainer('address');
		$address->addText('street');
		return $form;
	}


	public function test(): void
	{
		assertType(Nette\Forms\Container::class, $this['myForm']['address']);
		assertType(Nette\Forms\Controls\BaseControl::class, $this['myForm']['address']['street']);
		assertType(Nette\ComponentModel\IComponent::class, $this['myForm-address-street']);
	}
}


// Cross-class: form created in a factory method in another class
class FormFactory
{
	public function createForm(): Form
	{
		$form = new Form;
		$form->addText('name');
		$form->addSelect('country', 'Country:', []);
		$form->addSubmit('send');
		return $form;
	}
}

class CrossClassConsumer
{
	public function __construct(
		private readonly FormFactory $factory,
	) {
	}


	public function test(): void
	{
		$form = $this->factory->createForm();
		assertType(Nette\Forms\Controls\TextInput::class, $form['name']);
		assertType(Nette\Forms\Controls\SelectBox::class, $form['country']);
		assertType(Nette\Forms\Controls\SubmitButton::class, $form['send']);
	}
}


// Mixed: createComponentXxx() delegates to a factory method in another class
class MixedPresenter extends Nette\ComponentModel\Container implements ArrayAccess
{
	use Nette\ComponentModel\ArrayAccess;

	public function __construct(
		private FormFactory $factory,
	) {
	}


	public function createComponentMyForm(): Form
	{
		$form = $this->factory->createForm();
		$form->addButton('cancel', 'Cancel');
		return $form;
	}


	public function test(): void
	{
		$form = $this['myForm'];
		assertType(Nette\Forms\Controls\TextInput::class, $form['name']);
		assertType(Nette\Forms\Controls\SelectBox::class, $form['country']);
		assertType(Nette\Forms\Controls\SubmitButton::class, $form['send']);
		assertType(Nette\Forms\Controls\Button::class, $form['cancel']);
	}
}
