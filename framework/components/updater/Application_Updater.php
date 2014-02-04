<?php
namespace SAF\Framework;

/**
 * The application updater plugin detects if the application needs to be updated, and launch updates
 *
 * All updatable plugins should use the mustUpdate() method to know if they need to launch their update process.
 */
class Application_Updater implements Plugin
{

	//----------------------------------------------------------------------------------- $updatables
	/**
	 * An array of updatable classes and objects
	 *
	 * @var mixed[]
	 */
	private $updatables = array();

	//---------------------------------------------------------------------------------- addUpdatable
	/**
	 * Adds an updatable object or class to the elements that need to be updated at each update
	 *
	 * This can be called before the application updater plugin is registered, all updatable objects will be kept
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
	 */
	public function autoUpdate()
	{
		if ($this->mustUpdate()) {
			$this->update();
			$this->done();
		}
	}

	//------------------------------------------------------------------------------------------ done
	/**
	 * Tells the updater the update is done and application has been updated
	 *
	 * After this call, next call to mustUpdate() will return false, until next update is needed
	 */
	public function done()
	{
		@unlink("update");
	}

	//------------------------------------------------------------------------------------ mustUpdate
	/**
	 * Returns true if the application must be updated
	 *
	 * @return boolean
	 */
	public function mustUpdate()
	{
		return file_exists("update");
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the application updater plugin
	 * Called by the plugins registerer when the plugin is set
	 *
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod(
			array('SAF\Framework\Main_Controller', "runController"), array($this, "autoUpdate")
		);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Updates all registered updatable objects
	 *
	 * You should prefer call autoUpdate() to update the application only if needed
	 */
	public function update()
	{
		foreach ($this->updatables as $updatable) {
			call_user_func(array($updatable, "update"));
		}
	}

}
