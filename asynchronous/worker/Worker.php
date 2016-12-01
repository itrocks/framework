<?php

namespace ITRocks\Framework\Asynchronous;

use Exception;

/**
 * Worker to manage asynchronous task execution
 */
abstract class Worker
{

	//----------------------------------------------------------------------------------------- $task
	/**
	 * @link Object
	 * @store false
	 * @var Task
	 */
	public $task;

	//----------------------------------------------------------------------------------------- error
	/**
	 * Called when an error has occurred
	 * @param $e Exception Exception launched
	 */
	public function error(Exception $e) { }

	//--------------------------------------------------------------------------------------- execute
	/**
	 * Basic execution of worker
	 */
	protected abstract function execute();

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run worker
	 */
	public function run()
	{
		try {
			$this->task->started();
			$this->execute();
			$this->task->finished();
		}
		catch (Exception $e) {
			$this->task->error();
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return 'Worker';
	}

}
