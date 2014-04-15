<?php
namespace SAF\Framework\Tests;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Tests;
use SAF\Framework\Tools\Namespaces;

/**
 * Tests run controller
 */
class Run_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------ test
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$class_name  = $parameters->shift();
		$method_name = $parameters->shift();
		if (empty($class_name)) {
			(new Tests)->run();
		}
		elseif (empty($method_name)) {
			(new Tests)->runClass($class_name);
		}
		else {
			(new Tests)->runClass($class_name, $method_name);
		}
		return '<h3>ALL DONE</h3>';
	}

}
