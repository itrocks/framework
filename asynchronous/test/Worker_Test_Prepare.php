<?php

namespace ITRocks\Framework\Asynchronous\Test;

use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Asynchronous\Worker;

/**
 * Prepare list of task to execute for tests
 */
class Worker_Test_Prepare extends Worker
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'Prepare list of task';
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 */
	public function execute()
	{
		$number_of_tests = 30;
		for ($i =0; $i < $number_of_tests; $i++) {
			sleep(1);
			$task = $this->task->request->addTask(new Worker_Test(rand(1,7)));
			for ($j=0; $j < rand(0,9); $j++) {
				$this->task->request->addTask(new Worker_Test(rand(1,2)), $task);
			}
		}
	}

}
