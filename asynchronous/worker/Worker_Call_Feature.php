<?php

namespace ITRocks\Framework\Asynchronous\Worker;

use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Asynchronous\Worker;
use ITRocks\Framework\Controller\Main;

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
	public function execute()
	{
		(new Main())->runController($this->uri, $this->parameters);
	}

}
