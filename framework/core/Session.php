<?php
namespace SAF\Framework;

/**
 * A class to manage variables and objects that are kept for the session time
 */
class Session
{

	//-------------------------------------------------------------------------------------- $plugins
	/**
	 * @var array
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
			$_SESSION['Session'] = $set_current;
			return $set_current;
		}
		return $_SESSION['Session'];
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
		if (isset($_SESSION[$class_name])) {
			return $_SESSION[$class_name];
		}
		elseif ($create_default) {
			return $_SESSION[$class_name] = Builder::create($class_name);
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
		return $_SESSION;
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

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from session
	 *
	 * @param $object_class string | object
	 */
	public function remove($object_class)
	{
		unset($_SESSION[is_string($object_class) ? $object_class : get_class($object_class)]);
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
			$_SESSION[isset($class_name) ? $class_name : get_class($object)] = $object;
		}
	}

}
