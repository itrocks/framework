<?php

namespace ITRocks\Framework\Asynchronous\Test;

use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Asynchronous\Worker;

/**
 * Test worker
 * Just a worker who sleep 30 seconds for tests
 */
class Worker_Test extends Worker
{

	//----------------------------------------------------------------------------------------- $wait
	/**
	 * Wait x seconds
	 * @var integer
	 */
	public $wait = 0;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Worker_Test constructor.
	 * @param $wait integer Time to wait
	 */
	public function __construct($wait = null)
	{
		if (isset($wait)) {
			$this->wait = $wait;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return 'wait ' . $this->wait . ' seconds';
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 */
	public function execute()
	{
		sleep($this->wait);
	}

}
