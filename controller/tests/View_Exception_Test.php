<?php
namespace ITRocks\Framework\Controller\Tests;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Tests;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\View;

/**
 * View_Exception test on multiple-controller call
 */
class View_Exception_Test extends Test
{

	//-------------------------------------------------------------------------------------- testCall
	/**
	 * Test a call to a multiple controller
	 *
	 * @see Controller::runMultiple
	 */
	public function testCall()
	{
		$result = Main::$current->runController(
			View::link(Tests::class, Controller::MULTIPLE),
			[Parameter::AS_WIDGET => true, Parameter::IS_INCLUDED => true]
		);
		$result = str_replace([SP, TAB, LF], '', $result);
		$this->assume(__METHOD__, $result, '<ul><li>WORKING</li><li>CRASHED</li><li>WORKING</li></ul>');
	}

}
