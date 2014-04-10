<?php
namespace SAF\Framework;

use SAF\Framework\AOP\Include_Filter;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader implements IAutoloader
{

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public function autoload($class_name)
	{
		include_once Include_Filter::file($class_name);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader
	 */
	public function register()
	{
		spl_autoload_register([$this, 'autoload']);
	}

}
