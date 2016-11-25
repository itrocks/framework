<?php

namespace ITRocks\Framework\Asynchronous\Test;

use ITRocks\Framework\Asynchronous\Request;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 *
 */
class Test_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Parameters $parameters
	 * @param array      $form
	 * @param array      $files
	 * @param string     $class_name
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
	{
		$asynchronous = new Request('Test of asynchronous task');
		$asynchronous->addTask(new Worker_Test_Prepare());
		$asynchronous->start();
		return 'Test launched';
	}
}
