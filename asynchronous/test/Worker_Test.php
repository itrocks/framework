<?php

namespace ITRocks\Framework\Asynchronous\Test;

use Exception;
use ITRocks\Framework\Asynchronous;
use ITRocks\Framework\Asynchronous\Worker;

/**
 * Test worker
 * Just a worker who sleep 30 seconds for tests
 */
class Worker_Test extends Worker
{

	//---------------------------------------------------------------------------------------- $error
	/**
	 * @var string
	 */
	public $error = '';

	//----------------------------------------------------------------------------------------- $wait
	/**
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

	//----------------------------------------------------------------------------------------- error
	/**
	 * @param $e Exception
	 */
	public function error(Exception $e)
	{
		$this->error = $e->getMessage();
	}

	//--------------------------------------------------------------------------------------- execute
	/**
	 */
	public function execute()
	{
		if (rand(1,9) == 1) {
			throw new Exception('Random error');
		}
		sleep($this->wait);
	}

}
