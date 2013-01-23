<?php
namespace SAF\Framework;

class Session
{
	use Current { current as private pCurrent; }

	//--------------------------------------------------------------------------------------- current
	/**
	 * @param Session $set_current
	 * @return Session
	 */
	public static function current(Session $set_current = null)
	{
		return self::pCurrent($set_current);
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * Get the object of class $class_name from session
	 *
	 * @param string $class_name
	 * @return object | null
	 */
	public function get($class_name)
	{
		return isset($_SESSION[$class_name]) ? $_SESSION[$class_name] : null;
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
	 * @param string $class_name
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

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from session
	 *
	 * @param string | object $object_class
	 */
	public function remove($object_class)
	{
		unset($_SESSION[is_string($object_class) ? $object_class : get_class($object_class)]);
	}

	//------------------------------------------------------------------------------------- removeAny
	/**
	 * Remove any session variable that has $object_class as class or parent class
	 *
	 * @param string | object $object_class
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
	 * @param object $object can be null (then nothing is set)
	 * @param string $class_name if not set, object class will be the session object identifier
	 */
	public function set($object, $class_name = null)
	{
		if (isset($object)) {
			$_SESSION[isset($class_name) ? $class_name : get_class($object)] = $object;
		}
	}

	//----------------------------------------------------------------------------------------- start
	/**
	 * @return Session
	 */
	public static function start()
	{
		session_start();
		return is_null(self::current()) ? self::current(new Session()) : self::current();
	}

}
