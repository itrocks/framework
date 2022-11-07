<?php
namespace ITRocks\Framework\Tests;

use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\Test;
use PHPUnit\Framework\TestResult;
use PHPUnit\Framework\TestSuite;
use PHPUnit\Framework\Warning;
use PHPUnit\TextUI\ResultPrinter;
use Throwable;

/**
 * Tests HTML result printer
 */
class Tests_Html_Result_Printer implements ResultPrinter
{

	//-------------------------------------------------------------------------------------- addError
	public function addError(Test $test, Throwable $t, float $time) : void
	{
		// TODO: Implement addError() method.
	}

	//------------------------------------------------------------------------------------ addFailure
	public function addFailure(Test $test, AssertionFailedError $e, float $time) : void
	{
		// TODO: Implement addFailure() method.
	}

	//----------------------------------------------------------------------------- addIncompleteTest
	public function addIncompleteTest(Test $test, Throwable $t, float $time) : void
	{
		// TODO: Implement addIncompleteTest() method.
	}

	//---------------------------------------------------------------------------------- addRiskyTest
	public function addRiskyTest(Test $test, Throwable $t, float $time) : void
	{
		// TODO: Implement addRiskyTest() method.
	}

	//-------------------------------------------------------------------------------- addSkippedTest
	public function addSkippedTest(Test $test, Throwable $t, float $time) : void
	{
		// TODO: Implement addSkippedTest() method.
	}

	//------------------------------------------------------------------------------------ addWarning
	public function addWarning(Test $test, Warning $e, float $time) : void
	{
		// TODO: Implement addWarning() method.
	}

	//--------------------------------------------------------------------------------------- endTest
	public function endTest(Test $test, float $time) : void
	{
		// TODO: Implement endTest() method.
	}

	//---------------------------------------------------------------------------------- endTestSuite
	public function endTestSuite(TestSuite $suite) : void
	{
		// TODO: Implement endTestSuite() method.
	}

	//----------------------------------------------------------------------------------- printResult
	public function printResult(TestResult $result) : void
	{
		// TODO: Implement printResult() method.
	}

	//------------------------------------------------------------------------------------- startTest
	public function startTest(Test $test) : void
	{
		// TODO: Implement startTest() method.
	}

	//-------------------------------------------------------------------------------- startTestSuite
	public function startTestSuite(TestSuite $suite) : void
	{
		// TODO: Implement startTestSuite() method.
	}

	//----------------------------------------------------------------------------------------- write
	public function write(string $buffer) : void
	{
		// TODO: Implement write() method.
	}

}
