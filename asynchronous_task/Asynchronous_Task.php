<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Asynchronous_Task\Worker;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;
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
	//----------------------------------------------------------------------------------------- ERROR
	const ERROR = 'error';

	//-------------------------------------------------------------------------------------- FINISHED
	const FINISHED = 'finished';

	//----------------------------------------------------------------------------------- IN_PROGRESS
	const IN_PROGRESS = 'in_progress';

	//--------------------------------------------------------------------------------------- PENDING
	const PENDING = 'pending';

	//-------------------------------------------------------------------------------- $creation_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $creation_date;

	//----------------------------------------------------------------------------------- $begin_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $begin_date;

	//------------------------------------------------------------------------------------- $end_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $end_date;

	//--------------------------------------------------------------------------- $last_progress_date
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $last_progress_date;

	//--------------------------------------------------------------------------------- $max_progress
	/**
	 * @var integer
	 */
	public $max_progress;

	//------------------------------------------------------------------------------------- $progress
	/**
	 * @var integer
	 */
	public $progress;

	//-------------------------------------------------------------------------------- $short_message
	/**
	 * @var string
	 */
	public $short_message;

	//---------------------------------------------------------------------------------------- $state
	/**
	 * State of progress generation (if status is in_progress)
	 *
	 * @var string
	 */
	public $state;

	//--------------------------------------------------------------------------------------- $status
	/**
	 * Status of debit
	 *
	 * @values pending, in_progress, finished, error
	 * @var string
	 */
	public $status = self::PENDING;

	//--------------------------------------------------------------------------------------- $worker
	/**
	 * @link Object
	 * @store json
	 * @var Worker
	 */
	public $worker;

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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	function __toString()
	{
		return strval($this->worker);
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Launch asynchronous task
	 */
	public function start()
	{
		$this->creation_date = new Date_Time();
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

	//----------------------------------------------------------------------------- calculateDuration
	/**
	 * @return string
	 */
	public function calculateDuration()
	{
		$end_date = $this->end_date && !$this->end_date->isEmpty()
			? $this->end_date : new Date_Time();
		if ($end_date) {
			$diff = $this->begin_date->diff($end_date, true);
			$format = [];
			if ($diff->h) {
				$format[] = $diff->h . SP . Loc::tr('hour');
			}
			if ($diff->i) {
				$format[] = $diff->i . SP . Loc::tr('minutes');
			}
			if ($diff->s) {
				$format[] = $diff->s . SP . Loc::tr('seconds');
			}
			return join(SP, $format);
		}
		return '';
	}

}
