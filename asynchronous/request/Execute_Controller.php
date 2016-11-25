<?php

namespace ITRocks\Framework\Asynchronous\Request;

use Exception;
use ITRocks\Framework\Asynchronous\Request;
use ITRocks\Framework\Asynchronous\Task;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;

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
		/** @var $asynchronous Request */
		$asynchronous = $parameters->getMainObject();
		if ($asynchronous && $asynchronous instanceof Request) {
			$finish = false;
			while (!$finish) {
				/** @var $tasks Task[] */
				$tasks = Dao::search(
					['request' => $asynchronous, 'status' => Task::PENDING],
					$asynchronous::getTaskClass(),
					Dao::limit(100)
				);
				if ($tasks) {
					foreach ($tasks as $task) {
						if ($task->canExecute()) {
							try {
								$task->started();
								$task->execute();
								$task->finished();
							}
							catch (Exception $e) {
								$task->error($e);
							}
						}
					}
				}
				else {
					$finish = true;
				}
			}
		}
		return 'Executed';
	}

}
