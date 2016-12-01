<?php
namespace ITRocks\Framework\Asynchronous\Running;

use Exception;
use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Dao;

/**
 */
class Worker extends Asynchronous\Worker
{
	//----------------------------------------------------------------------------------------- $task
	/**
	 * @link Object
	 * @store false
	 * @var Task
	 */
	public $task;

	//--------------------------------------------------------------------------------------- execute
	/**
	 * Basic execution of worker
	 */
	protected function execute()
	{
		$finish = false;
		while (!$finish) {
			$request = $this->task->request->getRequestToRun();
			$tasks = $request->getTaskToExecute($this->task->group);
			if ($this->isStopped()) {
				throw new Execution_Stopped();
			}
			if ($tasks) {
				foreach ($tasks as $task) {
					if ($task->canExecute()) {
						$task->run();
					}
				}
			}
			if (!$tasks) {
				$finish = true;
			}
		}
	}

	//------------------------------------------------------------------------------------- isStopped
	/**
	 * @return boolean
	 */
	private function isStopped()
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
