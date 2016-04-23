<?php
namespace SAF\Framework;

use SAF\Framework\Controller\Main;
use SAF\Framework\Logger\Entry;
use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;

/**
 * A very simple logger plugin that logs start and stop dates, pids and duration of main calls
 */
class Logger implements Registerable
{

	//------------------------------------------------------------------------------------ $anti_loop
	/**
	 * @var integer
	 */
	private $anti_loop = 0;

	//------------------------------------------------------------------------------------ $log_entry
	/**
	 * @var Entry
	 */
	public $log_entry;

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [$this, 'start']);
		$aop->afterMethod( [Main::class, 'runController'], [$this, 'stop']);
	}

	//---------------------------------------------------------------------------------------- resume
	/**
	 * Write stop date, but this is not the final write.
	 * Call this sometimes when you execute a daemon script without time limit.
	 */
	public function resume()
	{
		if ($this->anti_loop) {
			$this->log_entry->resume();
			Dao::write($this->log_entry, [Dao::only(['duration', 'error_code', 'stop'])]);
		}
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Start logging : write pids and start-time
	 *
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 */
	public function start($uri, $get, $post, $files)
	{
		if (!$this->anti_loop) {
			$this->log_entry = new Entry($uri, $get, $post, $files);
			Dao::write($this->log_entry);
		}
		$this->anti_loop++;
	}

	//------------------------------------------------------------------------------------------ stop
	/**
	 * Stop logging : write end date-time and duration
	 */
	public function stop()
	{
		$this->anti_loop--;
		if (!$this->anti_loop) {
			$this->log_entry->stop();
			Dao::write($this->log_entry, [Dao::only(['duration', 'error_code', 'stop'])]);
		}
	}

}
