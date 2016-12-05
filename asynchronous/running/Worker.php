<?php
namespace ITRocks\Framework\Asynchronous\Running;

use Exception;
use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Dao;

/**
 */
abstract class Worker extends Asynchronous\Worker
{
	//----------------------------------------------------------------------------------------- $task
	/**
	 * @link Object
	 * @store false
	 * @var Task
	 */
	public $task;

	//------------------------------------------------------------------------------------- isStopped
	/**
	 * @return boolean
	 */
	public function isStopped()
	{
		/** @var $task Task */
		$task = Dao::read(Dao::getObjectIdentifier($this->task), get_class($this->task));
		if (!$task) {
			return true;
		}
		switch($task->status) {
			case Task::IN_PROGRESS:
			case Task::PENDING:
				return false;
			default:
				return true;
		}
	}

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
			if ($e instanceof Execution_Stopped) {
				$this->task->status = Task::STOPPED;
				Dao::write($this->task);
			}
			else {
				$this->task->error();
			}
		}
	}

}
