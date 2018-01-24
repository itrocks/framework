<?php
namespace ITRocks\Framework\Controller\Tests;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Tests\Test;

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
		$result = Main::$current->runController('/ITRocks/Framework/Controller/Tests/multiple');
		$this->assume(__METHOD__, $result, 'a b c');
	}

}
