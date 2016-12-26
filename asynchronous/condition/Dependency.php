<?php

namespace ITRocks\Framework\Asynchronous\Condition;

use ITRocks\Framework\Asynchronous\Condition;
use ITRocks\Framework\Asynchronous\Task;
use ITRocks\Framework\Locale\Loc;

/**
 *
 */
class Dependency extends Condition
{

	//---------------------------------------------------------------------------------- $task_depend
	/**
	 * @link Object
	 * @var Task
	 */
	public $task_depend;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Dependency_Condition constructor.
	 *
	 * @param $task Task
	 */
	public function __construct(Task $task = null)
	{
		if ($task) {
			$this->task_depend = $task;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return Loc::tr('Depend on') . SP . $this->task_depend;
	}

	//----------------------------------------------------------------------------------------- check
	/**
	 * Check if depend task is finished
	 *
	 * @return string
	 */
	public function check()
	{
		$status = self::VALID;
		if ($this->task_depend) {
			switch ($this->task_depend->status) {
				case Task::IN_PROGRESS:
				case Task::PENDING:
					$status = self::PENDING;
					break;
				case Task::ERROR:
					return self::PENDING_ERROR;
				case Task::FINISHED:
					break;
			}
		}
		return $status;
	}

}
