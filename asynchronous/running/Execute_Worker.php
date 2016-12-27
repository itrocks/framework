<?php

namespace ITRocks\Framework\Asynchronous\Running;

/**
 */
class Execute_Worker extends Worker
{
	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return 'Execute worker';
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * Execute task attributed at his group
	 */
	protected function execute()
	{
		$finish = false;
		upgradeMemoryLimit('2G');

		while (!$finish) {
			upgradeTimeLimit(600);
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
		}
	}

}
