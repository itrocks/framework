<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * A very simple logger plugin that logs start and stop dates, PIDs and duration of main calls
 */
class Logger implements Registerable
{
	use Has_Get;

	//------------------------------------------------------------------------------------ $anti_loop
	/**
	 * @var integer
	 */
	private int $anti_loop = 0;

	//------------------------------------------------------------------------------------ $log_entry
	/**
	 * @var Entry
	 */
	public Entry $log_entry;

	//--------------------------------------------------------------------------------- getIdentifier
	/**
	 * Gets the identifier of the current log id
	 * May be useful if you need to save click-context information
	 *
	 * @return mixed
	 */
	public function getIdentifier()
	{
		return isset($this->log_entry) ? Dao::getObjectIdentifier($this->log_entry) : null;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
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
			Dao::write($this->log_entry, Dao::only('duration', 'error_code', 'stop'));
		}
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * Start logging : write PIDs and start-time
	 *
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array[]
	 */
	public function start(string $uri, array $get, array $post, array $files)
	{
		if ($this->anti_loop) {
			return;
		}
		$this->log_entry = new Entry($uri, $get, $post, $files);
		Dao::write($this->log_entry);
		$this->anti_loop ++;
	}

	//------------------------------------------------------------------------------------------ stop
	/**
	 * Stop logging : write end date-time and duration
	 */
	public function stop()
	{
		$this->anti_loop --;
		if ($this->anti_loop) {
			return;
		}
		$this->log_entry->stop();
		Dao::write($this->log_entry, Dao::only('duration', 'memory_usage', 'stop'));
	}

}
