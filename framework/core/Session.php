<?php
namespace SAF\Framework;

use SAF\Plugins;
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
	 * @var Plugins\Manager
	 */
	public $plugins;

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param $set_current Session
	 * @return Session
	 */
	public static function current(Session $set_current = null)
	{
		if ($set_current) {
			$_SESSION['session'] = $set_current;
			return $set_current;
		}
		return $_SESSION['session'];
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
		$get = [];
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
		$current = $this->current[Application::class];
		// TODO parse current[1] between '"' and replace array with string if R work well
		$class_name = is_array($current) ? $current[0] : get_class($current);
		$application_name = substr($class_name, 0, strrpos($class_name, BS));
		return strtolower(substr($application_name, strrpos($application_name, BS) + 1));
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
		$data = ['current' => [], 'plugins' => $this->plugins];
		foreach ($this->current as $class_name => $object) {
			if (is_object($object)) {
				$object = [$class_name, serialize($object)];
			}
			$data['current'][$class_name] = $object;
		}
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
	 * @example 'PHPSESSID=6kldcf5gbuk0u34cmihlo9gl22'
	 * @param $prefix string You can prefix your SID with '?' or '&' to append it to an URI or URL
	 * @return string
	 */
	public static function sid($prefix = '')
	{
		return session_id() ? ($prefix . session_name() . '=' . session_id()) : '';
	}

	//----------------------------------------------------------------------------------- unserialize
	/**
	 * @param $serialized string
	 */
	public function unserialize($serialized)
	{
		$data = unserialize($serialized);
		$this->current = $data['current'];
		$this->plugins = $data['plugins'];
	}

}
