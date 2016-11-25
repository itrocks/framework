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
	public abstract function execute();

	//-------------------------------------------------------------------------------------- finished
	/**
	 * Called when task is finished
	 */
	public function finished() { }

	//--------------------------------------------------------------------------------------- started
	/**
	 * Called when task is started
	 */
	public function started() { }

}
