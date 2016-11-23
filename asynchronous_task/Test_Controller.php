<?php

namespace ITRocks\Framework\Asynchronous_Task;

use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Asynchronous_Task\Worker\Worker_Test;
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
		$asynchronous_task = new Asynchronous_Task(new Worker_Test());
		$asynchronous_task->start();
		return 'Test launched';
	}
}
