<?php
namespace SAF\Framework\Unit_Tests;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Method;

/**
 * Tests run controller
 */
class Tests_Run_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------ test
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		(new Tests)->run();
	}

}
