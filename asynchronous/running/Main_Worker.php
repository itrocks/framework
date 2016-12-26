<?php

namespace ITRocks\Framework\Asynchronous\Running;

use ITRocks\Framework\Dao;

/**
 */
class Main_Worker extends Worker
{
	//----------------------------------------------------------------------------------------- $task
	/**
	 * @link Object
	 * @store false
	 * @var Task
	 */
	public $task;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return 'Repartition worker';
	}

	//------------------------------------------------------------------------------ checkRunningTask
	/**
	 * Check if this execution task with this group number is running.
	 * If not running, launch again.
	 *
	 * @param $group integer
	 */
	public function checkRunningTask($group)
	{
		$task = $this->getRunningTask($group);
		if ($task) {
			if (in_array($task->status, [Task::STOPPED, Task::ERROR, Task::FINISHED])) {
				$task->status = 'pending';
				Dao::write($task, Dao::only('status'));
			}
			if ($task->status == 'pending') {
				$task->asynchronousLaunch();
			}
		}
		else {
			$task = new Task($group);
			$task->request = $this->task->request;
			$task->group = $group;
			$task->worker = new Execute_Worker();
			Dao::write($task);
			$task->asynchronousLaunch();
		}
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * Attribute tasks for all runners
	 * Note : it's better to pass messages by socket, but for the moment, it's stay simple
	 */
	protected function execute()
	{
		$finish = false;
		while (!$finish) {
			if ($this->isStopped()) {
				throw new Execution_Stopped();
			}
			upgradeTimeLimit(600);
			upgradeMemoryLimit('2G');
			$request = $this->task->request->getRequestToRun();
			if (!$request->isFinished()) {
				$task_repartition = $request->getTaskToExecute(0);
				if ($task_repartition) {
					for ($i = 1; $i <= $request->number_of_executions; $i++) {
						// check if all process has a job
						$task = $request->getTaskToExecute($i);
						if (!$task) {
							$task = $this->selectTask($task_repartition);
							if ($task) {
								$task->group = $i;
								Dao::write($task, Dao::only('group'));
							}
						}
						$this->checkRunningTask($i);
					}
				}
				else {
					// nothing to do, wait
					sleep(1);
				}
			}
			else {
				$finish = true;
				for ($i = 1; $i <= $request->number_of_executions; $i++) {
					$task = $this->getRunningTask($i);
					$task->status = Task::FINISHED;
					Dao::write($task, Dao::only('status'));
				}
			}
		}
	}

	//-------------------------------------------------------------------------------- getRunningTask
	/**
	 * Return running execution task with this group
	 *
	 * @param $group integer
	 * @return Task
	 */
	private function getRunningTask($group)
	{
		/** @var $task Task */
		$task = Dao::searchOne(['request' => $this->task->request, 'group' => $group], Task::class);
		return $task;
	}

	//------------------------------------------------------------------------------------ selectTask
	/**
	 * Select a task available in the list of tasks
	 *
	 * @param $task_repartition Task[]
	 * @return Task|null
	 */
	private function selectTask($task_repartition)
	{
		foreach ($task_repartition as $task) {
			if ($task->canExecute() && !$task->group) {
				return $task;
			}
		}
		return null;
	}

}
