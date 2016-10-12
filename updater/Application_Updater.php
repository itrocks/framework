<?php
namespace SAF\Framework\Updater;

use SAF\Framework\Application;
use SAF\Framework\Controller\Main;
use SAF\Framework\Controller\Needs_Main;
use SAF\Framework\Session;
use Serializable;

/**
 * The application updater plugin detects if the application needs to be updated, and launch updates
 * for all objects (independent or plugins) that process updates
 */
class Application_Updater implements Serializable
{

	//------------------------------------------------------------------------------ LAST_UPDATE_FILE
	const LAST_UPDATE_FILE = 'last_update';

	//----------------------------------------------------------------------------------- UPDATE_FILE
	const UPDATE_FILE = 'update';

	//------------------------------------------------------------------------------------ $lock_file
	/**
	 * Lock file handle
	 *
	 * @var resource
	 */
	private $lock_file;

	//----------------------------------------------------------------------------------- $updatables
	/**
	 * An array of updatable objects or class names
	 *
	 * @var Updatable[]|string[]
	 */
	private $updatables = [];

	//---------------------------------------------------------------------------------- $update_time
	/**
	 * @var integer
	 */
	private $update_time;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Application updater constructor zaps the cache directory if '?Z' argument is sent
	 * This will result into a complete application cache rebuild
	 */
	public function __construct()
	{
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
		$this->updatables[] = $object;
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
			$this->update($controller);
			$this->done();
			return true;
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
		$this->setLastUpdateTime($this->update_time);
		unset($this->update_time);
		flock($this->lock_file, LOCK_UN);
		fclose($this->lock_file);
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

	//------------------------------------------------------------------------------------ mustUpdate
	/**
	 * Returns true if the application must be updated
	 *
	 * @return boolean
	 */
	public function mustUpdate()
	{
		if (file_exists(self::UPDATE_FILE)) {
			// wait for update lock file to be released by another update in progress
			// then : locks the update file to avoid any other update
			$this->lock_file = fopen(self::UPDATE_FILE, 'r');
			while (file_exists(self::UPDATE_FILE) && !flock($this->lock_file, LOCK_EX)) {
				usleep(100000);
				clearstatcache(true, self::UPDATE_FILE);
			}
			if (file_exists(self::UPDATE_FILE)) {
				return true;
			}
			fclose($this->lock_file);
			// TODO ask for stop (update file does this job) and wait for running tasks to stop
		}
		return false;
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string the string representation of the object : only class names are kept
	 */
	public function serialize()
	{
		$updatables = [];
		foreach ($this->updatables as $updatable) {
			$updatables[] = is_object($updatable) ? get_class($updatable) : $updatable;
		}
		return serialize($updatables);
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
		$last_update_time = $this->getLastUpdateTime();
		if (!isset($this->update_time)) {
			$this->update_time = time();
		}
		foreach ($this->updatables as $key => $updatable) {
			if (is_string($updatable)) {
				$updatable = Session::current()->plugins->get($updatable);
				$this->updatables[$key] = $updatable;
			}
			if ($updatable instanceof Needs_Main) {
				$updatable->setMainController($main_controller);
			}
			$updatable->update($last_update_time);
		}
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string the string representation of the object
	 */
	public function unserialize($serialized)
	{
		$this->updatables = unserialize($serialized);
	}

}
