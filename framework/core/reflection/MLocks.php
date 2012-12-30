<?php
namespace SAF\Framework;

abstract class MLocks
{

	//------------------------------------------------------------------------------------------ lock
	/**
	 * Lock an object's method call
	 *
	 * @param object $object
	 * @param string $method_name
	 * @return boolean
	 */
	public static function lock($object, $method_name)
	{
		return Locks::lock("method:" . get_class($object) . "_" . $method_name);
	}

	//---------------------------------------------------------------------------------------- unlock
	/**
	 * Unlock an object's method call
	 *
	 * @param object $object
	 * @param string $method_name
	 */
	public static function unlock($object, $method_name)
	{
		Locks::unlock("method:" . get_class($object) . "_" . $method_name);
	}

}
