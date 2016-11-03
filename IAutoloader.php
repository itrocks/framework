<?php
namespace ITRocks\Framework;

/**
 * Autoloader interface
 */
interface IAutoloader
{

	//-------------------------------------------------------------------------------------- autoload
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public function autoload($class_name);

}
