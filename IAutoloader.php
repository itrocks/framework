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
	 * @return string automatically loaded file path
	 */
	public function autoload(string $class_name) : string;

}
