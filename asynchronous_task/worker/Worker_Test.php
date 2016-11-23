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
		$number_of_tests = 30;
		$this->initMaxProgress($asynchronous_task, $number_of_tests);
		for ($i =0; $i < $number_of_tests; $i++) {
			sleep(5);
			$this->progress($asynchronous_task, $i + 1, 'Wait step ' . $i . ' for 5 seconds');
		}
	}

	//-------------------------------------------------------------------------------------- finished
	/**
	 * @param Asynchronous_Task $asynchronous_task
	 * @param string            $short_message
	 */
	public function finished(
		Asynchronous_Task $asynchronous_task, $short_message = 'Test finished with success'
	)	{
		parent::finished($asynchronous_task, $short_message);
	}

}
