<?php

namespace ITRocks\Framework\Asynchronous_Task;

use Exception;
use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 */
class Execute_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		/** @var $asynchronous_task Asynchronous_Task */
		$asynchronous_task = $parameters->getMainObject();
		$worker = $asynchronous_task->worker;
		if ($asynchronous_task->status == Asynchronous_Task::PENDING) {
			$worker->started($asynchronous_task);
			try {
				$worker->execute($asynchronous_task);
				$worker->finished($asynchronous_task);
			}
			catch (Exception $e) {
				$worker->error($asynchronous_task);
			}
		}
		return 'Executed';
	}

}
