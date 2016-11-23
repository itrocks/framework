<?php

namespace ITRocks\Framework\Asynchronous_Task;

use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Worker to manage asynchronous task execution
 */
abstract class Worker
{

	//----------------------------------------------------------------------------------------- error
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 * @param $short_message      string Short message for users
	 */
	public function error(Asynchronous_Task $asynchronous_task, $short_message = '')
	{
		$asynchronous_task->end_date = new Date_Time();
		$asynchronous_task->status = Asynchronous_Task::ERROR;
		$asynchronous_task->short_message = $short_message;
		Dao::write($asynchronous_task, Dao::only(['status', 'end_date', 'short_message']));
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public abstract function execute(Asynchronous_Task $asynchronous_task);

	//-------------------------------------------------------------------------------------- finished
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 * @param $short_message      string Short message for users
	 */
	public function finished(Asynchronous_Task $asynchronous_task, $short_message = '')
	{
		$asynchronous_task->end_date = new Date_Time();
		$asynchronous_task->status = Asynchronous_Task::FINISHED;
		$asynchronous_task->short_message = $short_message;
		Dao::write($asynchronous_task, Dao::only(['status', 'end_date', 'short_message']));
	}

	//-------------------------------------------------------------------------------------- progress
	/**
	 * @param $asynchronous_task  Asynchronous_Task
	 * @param $progress           integer
	 * @param $short_message      string Short message for users
	 */
	public function progress(Asynchronous_Task $asynchronous_task, $progress, $short_message = '')
	{
		$asynchronous_task->progress = $progress;
		$asynchronous_task->last_progress_date = new Date_Time();
		$asynchronous_task->short_message = $short_message;
		$only = ['progress', 'last_progress_date', 'short_message'];
		Dao::write($asynchronous_task, Dao::only($only));
	}

	//-------------------------------------------------------------------------------------- progress
	/**
	 * @param $asynchronous_task  Asynchronous_Task
	 * @param $max_progress       integer
	 * @param $short_message      string Short message for users
	 */
	public function initMaxProgress(
		Asynchronous_Task $asynchronous_task, $max_progress, $short_message = ''
	) {
		$asynchronous_task->progress = 0;
		$asynchronous_task->max_progress = $max_progress;
		$asynchronous_task->last_progress_date = new Date_Time();
		$asynchronous_task->short_message = $short_message;
		$only = ['progress', 'max_progress', 'last_progress_date', 'short_message'];
		Dao::write($asynchronous_task, Dao::only($only));
	}

	//--------------------------------------------------------------------------------------- started
	/**
	 * @param $asynchronous_task  Asynchronous_Task
	 * @param $short_message      string Short message for users
	 */
	public function started(Asynchronous_Task $asynchronous_task, $short_message = '')
	{
		$asynchronous_task->status = Asynchronous_Task::IN_PROGRESS;
		$asynchronous_task->begin_date = new Date_Time();
		$asynchronous_task->short_message = $short_message;
		Dao::write($asynchronous_task, Dao::only(['status', 'begin_date', 'short_message']));
	}

}
