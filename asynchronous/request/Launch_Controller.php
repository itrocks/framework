<?php

namespace ITRocks\Framework\Asynchronous\Request;

use ITRocks\Framework\Asynchronous\Request;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Asynchronous\Running;
use ITRocks\Framework\Dao;

/**
 */
class Launch_Controller implements Feature_Controller
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
	public function run(Parameters $parameters, array $form, array $files)
	{
		/** @var $asynchronous Request */
		$asynchronous = $parameters->getMainObject();
		if ($asynchronous && $asynchronous instanceof Request) {
			$running_request = Running\Request::getRequest($asynchronous);
			foreach ($running_request->tasks as $task) {
				if (
					in_array(
						$task->status, [Running\Task::FINISHED, Running\Task::ERROR, Running\Task::STOPPED]
					)
				) {
					$task->status = Running\Task::PENDING;
					Dao::write($task, Dao::only('status'));
				}
			}
			$asynchronous->launch();
		}
		return (new List_Controller())->run($parameters, $form, $files, get_class($asynchronous));
	}

}
