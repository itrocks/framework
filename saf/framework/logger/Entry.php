<?php
namespace SAF\Framework\Logger;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Mysql\Link;
use SAF\Framework\Tools\Date_Time;

/**
 * Log class stores logs infos
 *
 * @set Logs
 */
class Entry
{

	//------------------------------------------------------------------------------------ $arguments
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $arguments;

	//------------------------------------------------------------------------------------- $duration
	/**
	 * Until stop() is not called, $duration contains the start microtime.
	 * After stop() is called, it contains the number of seconds between start and stop, with a
	 * precision near from the microsecond.
	 *
	 * @var float
	 */
	public $duration;

	//---------------------------------------------------------------------------------------- $files
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $files;

	//----------------------------------------------------------------------------------------- $form
	/**
	 * @max_length 65000
	 * @var string
	 */
	public $form;

	//------------------------------------------------------------------------------ $mysql_thread_id
	/**
	 * @var integer
	 */
	public $mysql_thread_id;

	//----------------------------------------------------------------------------------- $process_id
	/**
	 * @var integer
	 */
	public $process_id;

	//----------------------------------------------------------------------------------- $session_id
	/**
	 * @var string
	 */
	public $session_id;

	//---------------------------------------------------------------------------------------- $start
	/**
	 * @var Date_Time
	 */
	public $start;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * @var Date_Time
	 */
	public $stop;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * @max_length 255
	 * @var string
	 */
	public $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor initialises logged information for a call on script beginning.
	 *
	 * @param $uri       string
	 * @param $arguments array
	 * @param $form      array
	 * @param $files     array
	 */
	public function __construct($uri, $arguments = null, $form = null, $files = null)
	{
		if (!isset($this->start)) {
			$this->duration = microtime(true);
			$this->start = new Date_Time();
		}
		if (!isset($this->process_id)) {
			$this->process_id = getmypid();
		}
		if (!isset($this->mysql_thread_id)) {
			$dao = Dao::current();
			if ($dao instanceof Link) {
				$this->mysql_thread_id = $dao->getConnection()->thread_id;
			}
		}
		if (!isset($this->session_id)) {
			$this->session_id = session_id();
		}
		if (isset($arguments) && !isset($this->arguments)) {
			$this->arguments = $this->serialize($arguments);
		}
		if (isset($uri) && !isset($this->uri)) {
			$this->uri = $uri;
		}
		if (isset($files) && !isset($this->files)) {
			$this->files = $this->serialize($files);
		}
		if (isset($form) && !isset($this->form)) {
			if (isset($form['password'])) {
				$form['password'] = '***';
			}
			$this->form = $this->serialize($form);
		}
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @param $str string
	 * @return string
	 */
	private function serialize($str)
	{
		$str = json_encode($str);
		return ($str === '[]') ? '' : $str;
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		$this->duration = microtime(true) - $this->duration;
		$this->stop = new Date_Time();
	}

}
