<?php
namespace SAF\Framework;

/**
 * The application updater plugin detects if the application needs to be updated, and launch updates
 *
 * All updatable plugins should use the mustUpdate() method to know if they need to launch their update process.
 */
abstract class Application_Updater implements Plugin
{

	//----------------------------------------------------------------------------------- $updatables
	/**
	 * An array of updatable classes and objects
	 *
	 * @var mixed[]
	 */
	private static $updatables = array();

	//---------------------------------------------------------------------------------- addUpdatable
	/**
	 * Adds an updatable object or class to the elements that need to be updated at each update
	 *
	 * This can be called before the application updater plugin is registered, all updatable objects will be kept
	 *
	 * @param $object Updatable|string object or class name
	 */
	public static function addUpdatable($object)
	{
		self::$updatables[] = $object;
	}

	//------------------------------------------------------------------------------------ autoUpdate
	/**
	 * Check if application must be updated
	 *
	 * Update if update flag file found
	 * Does nothing if not
	 */
	public static function autoUpdate()
	{
		if (self::mustUpdate()) {
			self::update();
			self::done();
		}
	}

	//------------------------------------------------------------------------------------------ done
	/**
	 * Tells the updater the update is done and application has been updated
	 *
	 * After this call, next call to mustUpdate() will return false, until next update is needed
	 */
	public static function done()
	{
		@unlink("update");
	}

	//------------------------------------------------------------------------------------ mustUpdate
	/**
	 * Returns true if the application must be updated
	 *
	 * @return boolean
	 */
	public static function mustUpdate()
	{
		return file_exists("update");
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers the application updater plugin
	 *
	 * Called by the plugins registerer when the plugin is set
	 */
	public static function register()
	{
		Aop::addBeforeMethodCall(
			array('SAF\Framework\Main_Controller', "runController"), array(__CLASS__, "autoUpdate")
		);
	}

	//---------------------------------------------------------------------------------------- update
	/**
	 * Updates all registered updatable objects
	 *
	 * You should prefer call autoUpdate() to update the application only if needed
	 */
	public static function update()
	{
		foreach (self::$updatables as $updatable) {
			call_user_func(array($updatable, "update"));
		}
	}

}
