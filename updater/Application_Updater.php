<?php
namespace ITRocks\Framework\Updater;

use ITRocks\Framework\Application;
use ITRocks\Framework\Configuration\Environment;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Asynchronous;

/**
 * The application updater plugin detects if the application needs to be updated, and launch updates
 * for all objects (independent or plugins) that process updates
 *
 * Because of session (and so plugins) that can be reset and recreated in some process, we use only
 * static properties to be sure they are shared across instances.
 * This makes others plugin classes able to know state of update processing.
 *
 * TODO LOW see why this object is created several times during updates : should be only one of it
 */
class Application_Updater implements Configurable
{
	use Has_Get;

	//------------------------------------------------------------------ DELAY_BETWEEN_TWO_LOCK_TRIES
	const DELAY_BETWEEN_TWO_LOCK_TRIES = 'delay_between_two_lock_tries';

	//------------------------------------------------------------------------------ LAST_UPDATE_FILE
	const LAST_UPDATE_FILE = 'last_update';

	//--------------------------------------------------------------------------- NB_MAX_LOCK_RETRIES
	const NB_MAX_LOCK_RETRIES = 'nb_max_lock_retries';

	//------------------------------------------------------------------------------------ UPDATABLES
	const UPDATABLES = 'updatables';

	//----------------------------------------------------------------------------------- UPDATE_FILE
	const UPDATE_FILE = 'update';

	//----------------------------------------------------------------- $delay_between_two_lock_tries
	/**
	 * Delay between two lock tries in microseconds
	 *
	 * @example 1000000 (μs = 1s)
	 * @var integer
	 */
	private static int $delay_between_two_lock_tries = 1000000;

	//------------------------------------------------------------------------------------ $lock_file
	/**
	 * Lock file handle
	 *
	 * @var resource
	 */
	private static mixed $lock_file;

	//------------------------------------------------------------------------------------- $maintain
	/**
	 * Launch maintainer when update ends
	 *
	 * @var boolean
	 */
	public static bool $maintain = true;

	//-------------------------------------------------------------------------- $nb_max_lock_retries
	/**
	 * Maximum number of tries to lock file for update
	 *
	 * @example 240 (* 1000000μs = 4 minutes)
	 * @var integer
	 */
	private static int $nb_max_lock_retries = 300;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * Tells if update is running
	 *
	 * @var boolean
	 */
	private static bool $running = false;

	//----------------------------------------------------------------------------------- $updatables
	/**
	 * An array of updatable objects or class names
	 *
	 * @var Updatable[]|string[]
	 */
	private static array $updatables = [];

	//---------------------------------------------------------------------------------- $update_time
	/**
	 * @var ?integer
	 */
	private static ?int $update_time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Application updater constructor zaps the cache directory if '?Z' argument is sent
	 * This will result into a complete application cache rebuild
	 *
	 * @param $configuration array
	 */
	public function __construct(mixed $configuration = [])
	{
		$this->setConfiguration($configuration);

		if (isset($_GET['Z'])) {
			if (!isset($_POST['Z'])) {
				Main::$current->running = false;
				die($this->confirmFullUpdateView());
			}
			$file_name = $this->getLastUpdateFileName();
			clearstatcache(true, $file_name);
			if (file_exists($file_name)) {
				unlink($file_name);
			}
			touch(self::UPDATE_FILE);
		}
	}

	//----------------------------------------------------------------------------------- __serialize
	/**
	 * @return array the serialized representation of the object : only class names are kept
	 */
	public function __serialize() : array
	{
		$updatables = [];
		foreach (self::$updatables as $updatable) {
			$updatables[] = is_object($updatable) ? get_class($updatable) : $updatable;
		}
		return [
			self::DELAY_BETWEEN_TWO_LOCK_TRIES => self::$delay_between_two_lock_tries,
			self::NB_MAX_LOCK_RETRIES          => self::$nb_max_lock_retries,
			self::UPDATABLES                   => $updatables
		];
	}

	//--------------------------------------------------------------------------------- __unserialize
	/**
	 * @param $serialized array the string representation of the object
	 */
	public function __unserialize(array $serialized) : void
	{
		$this->setConfiguration($serialized);
	}

	//---------------------------------------------------------------------------------- addUpdatable
	/**
	 * Adds an updatable object or class to the elements that need to be updated at each update
	 *
	 * This can be called before the application updater plugin is registered, all updatable objects
	 * will be kept
	 *
	 * @param $object string|Updatable object or class name
	 */
	public function addUpdatable(string|Updatable $object) : void
	{
		/**
		 * This is called each time the plugin is registered (means on session creation/reset) and it
		 * can happen that some Updatable plugins do some session reset, so register several times
		 * during an update. Since we do not want to add updatables again, we add only if update is not
		 * already running
		 */
		if (!self::isRunning()) {
			self::$updatables[] = $object;
		}
	}

	//------------------------------------------------------------------------------------ autoUpdate
	/**
	 * Check if application must be updated
	 *
	 * Update if update flag file found
	 * Does nothing if not
	 *
	 * @param $controller Main
	 * @return boolean true if updates were made
	 * @throws Application_Updater_Exception
	 */
	public function autoUpdate(Main $controller) : bool
	{
		if ($this->mustUpdate()) {
			try {
				if ($this->lock()) {
					$this->update($controller);
					$this->release();
					$this->done();
					return true;
				}
				else {
					throw new Application_Updater_Exception('Unable to acquire lock');
				}
			}
			catch (Application_Updater_Exception $exception) {
				$this->release();
				throw $exception;
			}
		}
		return false;
	}

	//------------------------------------------------------------------------- confirmFullUpdateView
	/**
	 * Returns a 'full update' / RAZ form
	 *
	 * @return string
	 */
	private function confirmFullUpdateView() : string
	{
		// Does not use View, as it is not ready and this may crash if called at this step
		return strReplace(
			[
				'(memory_limit)' => ini_get('memory_limit'),
				'{time_limit}'   => ini_get('max_execution_time')
			],
			file_get_contents(__DIR__ . SL . 'Application_Updater_confirmFullUpdate.html')
		);
	}

	//------------------------------------------------------------------------------------------ done
	/**
	 * Tells the updater the update is done and application has been updated
	 *
	 * After this call, next call to mustUpdate() will return false, until next update is needed
	 */
	public function done() : void
	{
		$this->setLastUpdateTime(self::$update_time);
		self::$update_time = null;
		clearstatcache(true, self::UPDATE_FILE);
		if (file_exists(self::UPDATE_FILE) && Will_Call::isDone()) {
			unlink(self::UPDATE_FILE);
		}
		if (function_exists('opcache_reset')) {
			opcache_reset();
		}
		if (isset($_GET['Z']) && isset($_POST['Z'])) {
			Main::$current->running = false;
			die($this->fullUpdateDoneView());
		}
	}

	//---------------------------------------------------------------------------- fullUpdateDoneView
	/**
	 * Returns a 'full update' / RAZ form
	 *
	 * @return string
	 */
	private function fullUpdateDoneView() : string
	{
		// Does not use View, as it is not ready and this may crash if called at this step
		return file_get_contents(__DIR__ . SL . 'Application_Updater_fullUpdateDone.html');
	}

	//------------------------------------------------------------------------- getLastUpdateFileName
	/**
	 * @return string
	 */
	private function getLastUpdateFileName() : string
	{
		return Application::getCacheDir() . SL . self::LAST_UPDATE_FILE;
	}

	//----------------------------------------------------------------------------- getLastUpdateTime
	/**
	 * @return integer last compile time
	 */
	private function getLastUpdateTime() : int
	{
		$file_name = $this->getLastUpdateFileName();
		return file_exists($file_name) ? filemtime($file_name) : 0;
	}

	//------------------------------------------------------------------------------------- isRunning
	/**
	 * Tells if update is running
	 *
	 * @return boolean
	 */
	public function isRunning() : bool
	{
		return self::$running;
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Lock other script for update
	 *
	 * @return boolean
	 */
	private function lock() : bool
	{
		self::$lock_file = fopen(self::UPDATE_FILE, 'r');
		// wait for update lock file to be released by another update in progress then :
		// locks the update file to avoid any other update
		$nb_try      = 0;
		$would_block = true;
		while (
			($nb_try++ < self::$nb_max_lock_retries)
			&& file_exists(self::UPDATE_FILE)
			&& !flock(self::$lock_file, LOCK_EX | LOCK_NB, $would_block)
			&& $would_block
		) {
			usleep(self::$delay_between_two_lock_tries);
			clearstatcache(true, self::UPDATE_FILE);
		}
		return $nb_try < self::$nb_max_lock_retries;
	}

	//------------------------------------------------------------------------------------ mustUpdate
	/**
	 * Returns true if the application must be updated
	 *
	 * @return boolean
	 */
	public function mustUpdate() : bool
	{
		if (file_exists(self::UPDATE_FILE)) {
			return true;
		}
		return false;
	}

	//--------------------------------------------------------------------------------------- release
	/**
	 * Release the lock
	 */
	private function release() : void
	{
		if (self::$lock_file) {
			// Note: fclose() will also unlock the file, but it's proper to do it explicitly !
			flock(self::$lock_file, LOCK_UN);
			// TODO ask for stop (update file does this job) and wait for running tasks to stop
			fclose(self::$lock_file);
		}
	}

	//--------------------------------------------------------------------------------- runMaintainer
	/**
	 * @return $this
	 */
	public function runMaintainer() : static
	{
		if (self::$maintain && (Session::current()->environment !== Environment::DEVELOPMENT)) {
			$asynchronous = new Asynchronous();
			$asynchronous->call('/ITRocks/Framework/Dao/Mysql/maintain valid=1 verbose=1');
			$asynchronous->wait();
		}
		return $this;
	}

	//------------------------------------------------------------------------------ setConfiguration
	/**
	 * @param $configuration array
	 */
	protected function setConfiguration(array $configuration = []) : void
	{
		foreach ($configuration as $key => $value) {
			if (is_numeric($key)) {
				if (!in_array($value, self::$updatables)) {
					self::$updatables[] = $value;
				}
				trigger_error(
					'Root Application_Updater configuration' . print_r($configuration, true), E_USER_WARNING
				);
			}
			elseif (property_exists($this, $key)) {
				self::$$key = $value;
			}
			else {
				trigger_error(
					'Bad Application_Updater configuration ' . print_r($configuration, true), E_USER_WARNING
				);
			}
		}
	}

	//----------------------------------------------------------------------------- setLastUpdateTime
	/**
	 * @param $update_time integer
	 */
	private function setLastUpdateTime(int $update_time) : void
	{
		$updated = $this->getLastUpdateFileName();
		touch($updated, $update_time);
		/** @noinspection PhpUsageOfSilenceOperatorInspection may have been created by another one */
		@file_put_contents(Application::getCacheDir() . SL . '.htaccess', 'Deny From All');
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Updates all registered updatable objects
	 *
	 * You should prefer call autoUpdate() to update the application only if needed
	 *
	 * @param $main_controller Main
	 */
	public function update(Main $main_controller) : void
	{
		self::$running = true;

		$last_update_time = $this->getLastUpdateTime();
		if (!isset(self::$update_time)) {
			self::$update_time = time();
		}
		foreach (self::$updatables as $key => $updatable) {
			if (is_string($updatable)) {
				$updatable = Session::current()->plugins->get($updatable);
				self::$updatables[$key] = $updatable;
			}
			if ($updatable instanceof Needs_Main) {
				$updatable->setMainController($main_controller);
			}
			$updatable->update($last_update_time);
		}

		Will_Call::call();

		self::$running = false;
	}

}
