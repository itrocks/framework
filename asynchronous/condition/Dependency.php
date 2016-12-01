<?php

namespace ITRocks\Framework\Asynchronous\Condition;

use ITRocks\Framework\Asynchronous\Condition;
use ITRocks\Framework\Asynchronous\Task;

/**
 *
 */
class Dependency extends Condition
{

	//---------------------------------------------------------------------------------------- $tasks
	/**
	 * @link Object
	 * @var Task
	 */
	public $task;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Dependency_Condition constructor.
	 * @param $task Task
	 */
	public function __construct(Task $task = null)
	{
		if ($task) {
			$this->task = $task;
		}
	}

	//----------------------------------------------------------------------------------------- check
	/**
	 * @return string
	 */
	public function check()
	{
		$status = self::VALID;
		if ($this->task) {
			switch ($this->task->status) {
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
