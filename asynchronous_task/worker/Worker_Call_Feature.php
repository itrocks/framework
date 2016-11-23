<?php

namespace ITRocks\Framework\Asynchronous_Task\Worker;

use ITRocks\Framework\Asynchronous_Task;
use ITRocks\Framework\Asynchronous_Task\Worker;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Dao;

/**
 * This worker just call function
 * Notice : it's recommended to defined your own worker to manage progress and return.
 */
class Worker_Call_Feature extends Worker
{
	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var string[]
	 */
	public $parameters;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * @var string
	 */
	public $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Worker_Call_Feature constructor.
	 *
	 * @param $uri        string
	 * @param $parameters string[]
	 */
	public function __construct($uri, array $parameters)
	{
		$this->parameters = $parameters;
		$this->uri        = $uri;
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 * @param $asynchronous_task Asynchronous_Task
	 */
	public function execute(Asynchronous_Task $asynchronous_task)
	{
		$result = (new Main())->runController($this->uri, $this->parameters);
		$asynchronous_task->short_message = $result;
		Dao::write($asynchronous_task);
	}

}
