<?php declare(strict_types=1);

namespace Tests\Application;

use Nette\Application\AbortException;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Presenter;

class TestPresenter extends Presenter
{
	// BAD: catch (\Throwable) swallows AbortException
	public function actionBad(): void
	{
		try {
			$this->redirect('Home:');
		} catch (\Throwable $e) {
			// swallowed
		}
	}


	// OK: the catch rethrows
	public function actionRethrow(): void
	{
		try {
			$this->redirect('Home:');
		} catch (\Throwable $e) {
			throw $e;
		}
	}


	// OK: AbortException carved out before the broad catch
	public function actionCarvedOut(): void
	{
		try {
			$this->redirect('Home:');
		} catch (AbortException $e) {
			throw $e;
		} catch (\Throwable $e) {
			// fine, AbortException handled above
		}
	}


	// OK: the try block cannot throw AbortException
	public function actionNoAbort(): void
	{
		try {
			$length = strlen('foo');
		} catch (\Throwable $e) {
			// nothing aborting here
		}
	}


	// OK: a narrow catch does not catch AbortException (it lets it bubble up)
	public function actionNarrowCatch(): void
	{
		try {
			if ($this->getParameter('x') !== null) {
				$this->error(); // throws BadRequestException
			}
			$this->redirect('Home:'); // throws AbortException
		} catch (BadRequestException $e) {
			// catches BadRequestException only; AbortException bubbles up
		}
	}


	// OK: a generic @throws \Throwable method does not actually throw AbortException
	public function actionGenericThrowable(): void
	{
		try {
			$this->genericOperation();
		} catch (\Throwable $e) {
			// no AbortException can reach here
		}
	}


	// BAD: a throw hidden in a closure does not rethrow AbortException
	public function actionThrowInClosure(): void
	{
		try {
			$this->redirect('Home:');
		} catch (\Throwable $e) {
			$rethrow = function () use ($e): void {
				throw $e;
			};
		}
	}


	// OK: a dedicated catch (AbortException) is a deliberate swallow (e.g. Presenter::run() ending the lifecycle)
	public function actionDedicatedSwallow(): void
	{
		try {
			$this->redirect('Home:');
		} catch (AbortException $e) {
			// deliberately swallowed
		}
	}


	/** @throws \Throwable */
	private function genericOperation(): void
	{
		throw new \RuntimeException('x');
	}
}
