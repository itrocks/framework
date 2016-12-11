<?php
namespace ITRocks\Framework\Logger;

use ITRocks\Framework;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Link;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Log class stores logs infos
 *
 * @business
 * @representative start, uri
 * @set Logs
 */
class Entry
{
	// TODO HIGH #71516 Fix Builder\Compiler as this should be replaced by dynamic call in config.php
	use Framework\Dao\Mysql\File_Logger\Entry;
	use Framework\View\Logger\Entry;

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

	//------------------------------------------------------------------------------- $duration_start
	/**
	 * This is the microtime when the script starts.
	 * Used to calculate duration on resume() and stop()
	 *
	 * @store false
	 * @var float
	 */
	private $duration_start;

	//----------------------------------------------------------------------------------- $error_code
	/**
	 * Error code enable to know how the script stopped
	 *
	 * @see Error_Code
	 * @var integer
	 */
	private $error_code;

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
	 * @link DateTime
	 * @var Date_Time
	 */
	public $start;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * @link DateTime
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
	 * @param $files     array[]
	 */
	public function __construct(
		$uri = null, array $arguments = null, array $form = null, array $files = null
	) {
		if (isset($uri)) {
			if (!isset($this->start)) {
				$this->duration_start = microtime(true);
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
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim(Loc::dateToLocale($this->start) . SP . $this->uri);
	}

	//---------------------------------------------------------------------------------------- resume
	public function resume()
	{
		$this->stop();
		$this->error_code = Error_Code::RUNNING;
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
		$this->error_code = Error_Code::OK;
		$this->duration = microtime(true) - $this->duration_start;
		$this->stop = new Date_Time();
	}

}
