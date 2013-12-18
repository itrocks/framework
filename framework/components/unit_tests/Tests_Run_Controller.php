<?php
namespace SAF\Framework\Unit_Tests;

use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;
use SAF\Framework\Namespaces;

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
		$class_name  = $parameters->shift();
		$method_name = $parameters->shift();
		if (empty($class_name)) {
			(new Tests)->run();
		}
		elseif (empty($method_name)) {
			(new Tests)->runClass(Namespaces::fullClassName($class_name));
		}
		else {
			(new Tests)->runClass(Namespaces::fullClassName($class_name), $method_name);
		}
	}

}
