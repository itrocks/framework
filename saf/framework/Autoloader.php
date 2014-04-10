<?php
namespace SAF\Framework;

use SAF\Framework\AOP\Include_Filter;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader
{

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 * @return boolean
	 */
	public function autoload($class_name)
	{
		if ($i = strrpos($class_name, '\\')) {
			$namespace = strtolower(str_replace('\\', '/', substr($class_name, 0, $i)));
			$file_name = substr($class_name, $i + 1);
			// 'A\Class' stored into 'a/class/Class.php'
			if (
				is_file($file1 = strtolower($namespace . '/' . $file_name) . '/' . $file_name . '.php')
			) {
				return include_once(Include_Filter::file($file1));
			}
			// 'A\Class' stored into 'a/Class.php'
			elseif (is_file($file2 = strtolower($namespace) . '/' . $file_name . '.php')) {
				return include_once(Include_Filter::file($file2));
			}
			else {
				trigger_error(
					'Class not found ' . $class_name . ', should be into ' . $file1 . ' or ' . $file2,
					E_USER_ERROR
				);
			}
		}
		// 'A_Class' stored into 'A_Class.php'
		return include_once(Include_Filter::file($class_name . '.php'));
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
