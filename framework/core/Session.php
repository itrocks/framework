<?php
namespace SAF\Framework;

use Serializable;

/**
 * A class to manage variables and objects that are kept for the session time
 */
class Session implements Serializable
{

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var object[]|string[]
	 */
	private $current;

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var array
	 */
	public $plugins;

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * @param $level string
	 */
	public function activatePlugins($level = null)
	{
		if (isset($level)) {
			foreach (array_keys($this->plugins[$level]) as $class_name) {
				$this->getOnePlugin($class_name, $level);
			}
		}
		else {
			foreach ($this->plugins as $level => $plugins) {
				foreach (array_keys($plugins) as $class_name) {
					$this->getOnePlugin($class_name, $level);
				}
			}
		}
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Session
	 * @return Session
	 */
	public static function current(Session $set_current = null)
	{
		if ($set_current) {
			$_SESSION["session"] = $set_current;
			return $set_current;
		}
		return $_SESSION["session"];
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get the object of class $class_name from session
	 *
	 * @param $class_name     string
	 * @param $create_default boolean Create a default object for the class name if does not exist
	 * @return object|null
	 */
	public function get($class_name, $create_default = false)
	{
		if (isset($this->current[$class_name])) {
			$current = $this->current[$class_name];
			if (is_array($current)) {
				//$class_name = $current[0]; // TODO Check if R: and r: work well
				$current    = $current[1];
				$this->current[$class_name] = $current = unserialize($current);
			}
			return $current;
		}
		elseif ($create_default) {
			return $this->current[$class_name] = Builder::create($class_name);
		}
		else {
			return null;
		}
	}

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Get all objects from session
	 *
	 * @return object[] index is class name, value is an object
	 */
	public function getAll()
	{
		return $this->current;
	}

	//---------------------------------------------------------------------------------------- getAny
	/**
	 * Get all objects from session having $class_name as class or parent class
	 *
	 * @param $class_name string
	 * @return object[] key is the class name of the object
	 */
	public function getAny($class_name)
	{
		$get = array();
		foreach ($this->getAll() as $key => $value) {
			if (isset(class_parents($key)[$class_name])) {
				$get[$key] = $value;
			}
		}
		return $get;
	}

	//-------------------------------------------------------------------------------- getApplication
	/**
	 * Gets the current application name without having to unserialize it if serialized
	 * @return Application
	 */
	public function getApplicationName()
	{
		$current = $this->current['SAF\Framework\Application'];
		// TODO parse current[1] between '"' and replace array with string if R work well
		$class_name = is_array($current) ? $current[0] : get_class($current);
		$application_name = substr($class_name, 0, strrpos($class_name, "\\"));
		return strtolower(substr($application_name, strrpos($application_name, "\\") + 1));
	}

	//---------------------------------------------------------------------------------- getOnePlugin
	/**
	 * @param $class_name   string
	 * @param $level        string
	 * @return Plugin
	 */
	private function getOnePlugin($class_name, $level)
	{
		/** @var $plugin Plugin|boolean|string */
		$plugin = $this->plugins[$level][$class_name];
		// unserialize plugin
		if (!is_object($plugin)) {
			$serialized = $plugin;
			// serialized as true : the plugin can be created without configuration
			if ($serialized === true) {
				$plugin = Builder::create($class_name);
			}
			// serializable plugin
			elseif (is_a($class_name, 'Serializable', true)) {
				$plugin = unserialize($serialized);
			}
			// the plugin has not been registered yet : called by Aop_Dealer, no problemo
			elseif (is_array($serialized)) {
				$plugin = null;
			}
			// standard plugin serialization is "configuration only"
			else {
				$plugin_configuration = unserialize($serialized);
				$plugin = Builder::create($class_name, array($plugin_configuration));
				/** @noinspection PhpUndefinedFieldInspection */
				$plugin->plugin_configuration = $plugin_configuration;
			}
			// activate plugin
			if ($plugin instanceof Activable_Plugin) {
				/** @var $plugin Activable_Plugin */
				$plugin->activate();
			}
			$this->plugins[$level][$class_name] = $plugin;
		}
		return $plugin;
	}

	//------------------------------------------------------------------------------------- getPlugin
	/**
	 * @param $class_name string if null, get all plugins
	 * @param $level      string if null, search plugin into all levels. if false, don't throw error
	 * @return Plugin
	 */
	public function getPlugin($class_name, $level = null)
	{
		if (isset($this->plugins)) {
			if (is_string($level)) {
				return $this->getOnePlugin($class_name, $level);
			}
			else {
				foreach ($this->plugins as $plugins_level => $plugins) {
					if (isset($plugins[$class_name])) {
						$plugin = $this->getOnePlugin($class_name, $plugins_level);
						return $plugin;
					}
				}
				if ($level !== false) {
					trigger_error("Plugin $class_name not found", E_USER_ERROR);
				}
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from session
	 *
	 * @param $object_class string | object
	 */
	public function remove($object_class)
	{
		unset($this->current[is_string($object_class) ? $object_class : get_class($object_class)]);
	}

	//------------------------------------------------------------------------------------- removeAny
	/**
	 * Remove any session variable that has $object_class as class or parent class
	 *
	 * @param $object_class string | object
	 */
	public function removeAny($object_class)
	{
		$class_name = is_string($object_class) ? $object_class : get_class($object_class);
		$this->remove($class_name);
		foreach ($this->getAll() as $key => $value) {
			if (class_exists($key) && isset(class_parents($key)[$class_name])) {
				$this->remove($key);
			}
		}
	}

	//------------------------------------------------------------------------------------- serialize
	/**
	 * @return string
	 */
	public function serialize()
	{
		$data = array("current" => array());
		foreach ($this->current as $class_name => $object) {
			if (is_object($object)) {
				$object = array($class_name, serialize($object));
			}
			$data["current"][$class_name] = $object;
		}
		foreach ($this->plugins as $level => $plugins) {
			foreach ($plugins as $class_name => $object) {
				if (is_object($object)) {
					if ($object instanceof Serializable) {
						$data["plugins"][$level][$class_name] = serialize($object);
					}
					elseif (isset($object->plugin_configuration)) {
						$data["plugins"][$level][$class_name] = serialize($object->plugin_configuration);
					}
					else {
						$data["plugins"][$level][$class_name] = true;
					}
				}
				else {
					$data["plugins"][$level][$class_name] = $object;
				}
			}
		}
echo "<pre>serialized = " . print_r($data, true) . "</pre>";
		return serialize($data);
	}

	//------------------------------------------------------------------------------------------- set
	/**
	 * Set a session's object
	 *
	 * @param $object object|mixed can be null (then nothing is set)
	 * @param $class_name string if not set, object class is be the object identifier. Can be a free string too
	 */
	public function set($object, $class_name = null)
	{
		if (isset($object)) {
			$this->current[isset($class_name) ? $class_name : get_class($object)] = $object;
		}
	}

	//------------------------------------------------------------------------------------------- sid
	/**
	 * Returns current SID
	 *
	 * @example "PHPSESSID=6kldcf5gbuk0u34cmihlo9gl22"
	 * @param $prefix string You can prefix your SID with "?" or "&" to append it to an URI or URL
	 * @return string
	 */
	public static function sid($prefix = "")
	{
		return session_id() ? ($prefix . session_name() . "=" . session_id()) : "";
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->current = $data["current"];
		$this->plugins = $data["plugins"];
	}

}
