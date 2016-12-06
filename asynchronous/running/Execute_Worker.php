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
	 * Basic execution of worker
	 */
	protected function execute()
	{
		$finish = false;
		while (!$finish) {
			upgradeTimeLimit(600);
			upgradeMemoryLimit('2G');
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
