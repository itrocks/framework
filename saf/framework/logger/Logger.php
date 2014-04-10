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

	//------------------------------------------------------------------------------------- $antiloop
	/**
	 * @var integer
	 */
	private $antiloop = 0;

	//------------------------------------------------------------------------------------ $log_entry
	/**
	 * @var Entry
	 */
	private $log_entry;

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
		if (!$this->antiloop) {
			$this->log_entry = new Entry($uri, $get, $post, $files);
			Dao::write($this->log_entry);
		}
		$this->antiloop ++;
	}

	//------------------------------------------------------------------------------------------ stop
	/**
	 * Stop logging : write end date-time and duration
	 */
	public function stop()
	{
		$this->antiloop--;
		if (!$this->antiloop) {
			$this->log_entry->stop();
			Dao::write($this->log_entry);
		}
	}

}
