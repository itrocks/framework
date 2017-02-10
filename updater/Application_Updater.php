<?php
namespace ITRocks\Framework\Updater;

use Exception;
use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Needs_Main;
use ITRocks\Framework\Plugin\Configurable;
use ITRocks\Framework\Session;
use Serializable;

/**
 * The application updater plugin detects if the application needs to be updated, and launch updates
 * for all objects (independent or plugins) that process updates
 *
 * Because of session (and so plugins) that can be reset and recreated in some process, we use only
 * static properties to be sure they are shared across instances.
 * This makes others plugin classes able to know state of update processing.
 */
class Application_Updater implements Configurable, Serializable
{

	//------------------------------------------------------------------------------ LAST_UPDATE_FILE
	const LAST_UPDATE_FILE = 'last_update';

	//----------------------------------------------------------------------------- NB_MAX_LOCK_TRIES
	const NB_MAX_LOCK_TRIES = 'nb_max_lock_tries';

	//------------------------------------------------------------------ DELAY_BETWEEN_TWO_LOCK_TRIES
	const DELAY_BETWEEN_TWO_LOCK_TRIES = 'delay_between_two_lock_tries';

	//----------------------------------------------------------------------------------- UPDATE_FILE
	const UPDATE_FILE = 'update';

	//------------------------------------------------------------------- $delay_between_two_lock_try
	/**
	 * Delay between two lock tries in microseconds
	 *
	 * @example 1000000 (μs = 1s)
	 * @var integer
	 */
	private static $delay_between_two_lock_tries = 1000000;

	//------------------------------------------------------------------------------------ $lock_file
	/**
	 * Lock file handle
	 *
	 * @var resource
	 */
	private static $lock_file;

	//------------------------------------------------------------------------------ $nb_max_lock_try
	/**
	 * Maximum number of tries to lock file for update
	 *
	 * @example 240 (* 1000000μs = 4 minutes)
	 * @var integer
	 */
	private static $nb_max_lock_try = 240;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * Tells if update is running
	 *
	 * @var boolean
	 */
	private static $running = false;

	//----------------------------------------------------------------------------------- $updatables
	/**
	 * An array of updatable objects or class names
	 *
	 * @var Updatable[]|string[]
	 */
	private static $updatables;

	//---------------------------------------------------------------------------------- $update_time
	/**
	 * @var integer
	 */
	private static $update_time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Application updater constructor zaps the cache directory if '?Z' argument is sent
	 * This will result into a complete application cache rebuild
	 *
	 * @param $configuration array
	 */
	public function __construct($configuration)
	{
		if (isset($configuration[self::NB_MAX_LOCK_TRIES])) {
			self::$nb_max_lock_try = $configuration[self::NB_MAX_LOCK_TRIES];
		}
		if (isset($configuration[self::NB_MAX_LOCK_TRIES])) {
			self::$delay_between_two_lock_tries = $configuration[self::DELAY_BETWEEN_TWO_LOCK_TRIES];
		}

		if (!isset(self::$updatables)) {
			self::$updatables = [];
		}
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

	//---------------------------------------------------------------------------------- addUpdatable
	/**
	 * Adds an updatable object or class to the elements that need to be updated at each update
	 *
	 * This can be called before the application updater plugin is registered, all updatable objects
	 * will be kept
	 *
	 * @param $object Updatable|string object or class name
	 */
	public function addUpdatable($object)
	{
		self::$updatables[] = $object;
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
	 */
	public function autoUpdate(Main $controller)
	{
		if ($this->mustUpdate()) {
			try {
				if ($this->lock()) {
					$this->update($controller);
					$this->release();
					$this->done();
					return true;
				} else {
					throw new Exception("unable to acquire lock");
				}
			}
			catch (Exception $e) {
				$this->release();
				trigger_error("Unable to update : " . $e->getMessage(), E_USER_ERROR);
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
	private function confirmFullUpdateView()
	{
		// Does not use View, as it is not ready and this may crash if called at this step
		return file_get_contents(__DIR__ . SL . 'Application_Updater_confirmFullUpdate.html');
	}

	//------------------------------------------------------------------------------------------ done
	/**
	 * Tells the updater the update is done and application has been updated
	 *
	 * After this call, next call to mustUpdate() will return false, until next update is needed
	 */
	public function done()
	{
		$this->setLastUpdateTime(self::$update_time);
		self::$update_time = null;
		clearstatcache(true, self::UPDATE_FILE);
		if (file_exists(self::UPDATE_FILE)) {
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
	private function fullUpdateDoneView()
	{
		// Does not use View, as it is not ready and this may crash if called at this step
		return file_get_contents(__DIR__ . SL . 'Application_Updater_fullUpdateDone.html');
	}

	//------------------------------------------------------------------------- getLastUpdateFileName
	/**
	 * @return string
	 */
	private function getLastUpdateFileName()
	{
		return Application::current()->getCacheDir() . SL . self::LAST_UPDATE_FILE;
	}

	//----------------------------------------------------------------------------- getLastUpdateTime
	/**
	 * @return integer last compile time
	 */
	private function getLastUpdateTime()
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
	public function isRunning() {
		return self::$running;
	}

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Lock other script for update
	 */
	private function lock()
	{
		self::$lock_file = fopen(self::UPDATE_FILE, 'r');
		// wait for update lock file to be released by another update in progress
		// then : locks the update file to avoid any other update
		$nb_try = 0;
		$would_block = 1;
		while (
			$nb_try++ < self::$nb_max_lock_try
			&& file_exists(self::UPDATE_FILE)
			// add LOCK_NB to make a not blocking call, and check $would_block for lock acquired
			&& !flock(self::$lock_file, LOCK_EX | LOCK_NB, $would_block)
			&& $would_block
		) {
			usleep(self::$delay_between_two_lock_tries);
			clearstatcache(true, self::UPDATE_FILE);
		}
		if (!$would_block && file_exists(self::UPDATE_FILE)) {
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------ mustUpdate
	/**
	 * Returns true if the application must be updated
	 *
	 * @return boolean
	 */
	public function mustUpdate()
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
	private function release()
	{
		if (self::$lock_file) {
			// Note: fclose() will also unlock the file, but it's proper do do it explicitly !
			flock(self::$lock_file, LOCK_UN);
			// TODO ask for stop (update file does this job) and wait for running tasks to stop
			fclose(self::$lock_file);
		}
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string the string representation of the object : only class names are kept
	 */
	public function serialize()
	{
		$configuration = [];
		$configuration[self::DELAY_BETWEEN_TWO_LOCK_TRIES] = self::$delay_between_two_lock_tries;
		$configuration[self::NB_MAX_LOCK_TRIES] = self::$nb_max_lock_try;
		$updatables = [];
		foreach (self::$updatables as $updatable) {
			$updatables[] = is_object($updatable) ? get_class($updatable) : $updatable;
		}
		$configuration['updatables'] = $updatables;
		return serialize($configuration);
	}

	//----------------------------------------------------------------------------- setLastUpdateTime
	/**
	 * @param $update_time integer
	 */
	private function setLastUpdateTime($update_time)
	{
		$updated = $this->getLastUpdateFileName();
		touch($updated, $update_time);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Updates all registered updatable objects
	 *
	 * You should prefer call autoUpdate() to update the application only if needed
	 *
	 * @param $main_controller Main
	 */
	public function update(Main $main_controller)
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

		self::$running = false;
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string the string representation of the object
	 */
	public function unserialize($serialized)
	{
		$configuration = unserialize($serialized);
		if (isset($configuration[self::NB_MAX_LOCK_TRIES])) {
			self::$nb_max_lock_try = $configuration[self::NB_MAX_LOCK_TRIES];
		}
		if (isset($configuration[self::NB_MAX_LOCK_TRIES])) {
			self::$delay_between_two_lock_tries = $configuration[self::DELAY_BETWEEN_TWO_LOCK_TRIES];
		}
		self::$updatables = $configuration['updatables'];
	}

}
