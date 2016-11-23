<?php

namespace ITRocks\Framework\Asynchronous_Task\Worker;

use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Asynchronous_Task\Worker;

/**
 * Test worker
 * Just a worker who sleep 30 seconds for tests
 */
class Worker_Test extends Worker
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'Test worker';
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public function execute(Asynchronous_Task $asynchronous_task)
	{
		for ($i =0; $i < 30; $i++) {
			sleep(1);
			$asynchronous_task->progress++;
		}
		$asynchronous_task->progress++;
	}

}
