<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Asynchronous_Task\Worker;
use ITRocks\Framework\Tools\Paths;

/**
 * Asynchronous task
 * This module allow to manage asynchronous task with the progress (For heavy tasks)
 *
 * @business
 * @features
 */
class Asynchronous_Task
{
	//--------------------------------------------------------------------------------------- PENDING
	const PENDING = 'pending';

	//----------------------------------------------------------------------------------- IN_PROGRESS
	const IN_PROGRESS = 'in_progress';

	//-------------------------------------------------------------------------------------- FINISHED
	const FINISHED = 'finished';

	//----------------------------------------------------------------------------------------- ERROR
	const ERROR = 'error';

	//--------------------------------------------------------------------------------------- $worker
	/**
	 * @link Object
	 * @store json
	 * @var Worker
	 */
	public $worker;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * Status of debit
	 *
	 * @values pending, in_progress, finished, error
	 * @var string
	 */
	public $status = self::PENDING;

	//---------------------------------------------------------------------------------------- $state
	/**
	 * State of progress generation (if status is in_progress)
	 *
	 * @var string
	 */
	public $state;

	//------------------------------------------------------------------------------------- $progress
	/**
	 * @var integer
	 */
	public $progress;

	//--------------------------------------------------------------------------------- $max_progress
	/**
	 * @var integer
	 */
	public $max_progress;

	//-------------------------------------------------------------------------------- $short_message
	/**
	 * @var string
	 */
	public $short_message;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Asynchronous_Task constructor.
	 * @param $worker Worker
	 */
	public function __construct(Worker $worker = null)
	{
		if ($worker) {
			$this->worker = $worker;
		}
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Launch asynchronous task
	 */
	public function start()
	{
		Dao::write($this);
		$host = $_SERVER['HTTP_HOST'];
		$controller_url = $host . Paths::$uri_root . Paths::$script_name
			. View::link($this, 'execute');
		$curl = curl_init();
		curl_setopt($curl, CURLOPT_URL, $controller_url);
		curl_setopt($curl, CURLOPT_HEADER, 0);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, false);
		curl_setopt($curl, CURLOPT_TIMEOUT_MS, 100);
		curl_exec($curl);
		curl_close($curl);
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return $this->worker . ' ' . $this->status;
	}

}
