<?php

namespace ITRocks\Framework\Asynchronous_Task;

use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Dao;

/**
 * Worker to manage asynchronous task execution
 */
abstract class Worker
{

	//----------------------------------------------------------------------------------------- error
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public function error(Asynchronous_Task $asynchronous_task)
	{
		$asynchronous_task->status = Asynchronous_Task::ERROR;
		Dao::write($asynchronous_task, Dao::only(['status']));
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public abstract function execute(Asynchronous_Task $asynchronous_task);

	//-------------------------------------------------------------------------------------- finished
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public function finished(Asynchronous_Task $asynchronous_task)
	{
		$asynchronous_task->status = Asynchronous_Task::FINISHED;
		Dao::write($asynchronous_task, Dao::only(['status']));
	}

	//-------------------------------------------------------------------------------------- progress
	/**
	 * @param $asynchronous_task  Asynchronous_Task
	 * @param $progress           integer
	 */
	public function progress(Asynchronous_Task $asynchronous_task, $progress)
	{
		$asynchronous_task->progress = $progress;
		Dao::write($asynchronous_task, Dao::only(['status', 'progress']));
	}

	//--------------------------------------------------------------------------------------- started
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public function started(Asynchronous_Task $asynchronous_task)
	{
		$asynchronous_task->status = Asynchronous_Task::IN_PROGRESS;
		Dao::write($asynchronous_task, Dao::only(['status']));
	}

}
