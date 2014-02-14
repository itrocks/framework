<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
class Autoloader implements Plugins\Activable, IAutoloader
{

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		spl_autoload_register(array($this, "autoload"));
	}

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public function autoload($class_name)
	{
		if ($this->includeClass($class_name)) {
			if (is_a($class_name, Plugins\Plugin::class, true)) {
				Session::current()->plugins->get($class_name);
			}
		}
	}

	//---------------------------------------------------------------------------------- includeClass
	/**
	 * @param $class_name string The class name
	 * @param $file_path  string The file path. If null, this will be automatically searched into include path
	 * @return string the file path, if class was included. null if not.
	 */
	public function includeClass($class_name, $file_path = null)
	{
		if (!isset($file_path)) {
			$file_path = stream_resolve_include_path(Namespaces::shortClassName($class_name) . ".php");
		}
		if ($file_path && !Namespaces::checkFilePath($class_name, $file_path)) {
			$file_path = Namespaces::resolveFilePath($class_name);
		}
		if ($file_path) {
			/** @noinspection PhpIncludeInspection */
			include_once $file_path;
		}
		return $file_path;
	}

	//------------------------------------------------------------------------------ rectifyClassName
	/**
	 * @param $class_name string
	 * @param $file_path  string
	 * @return string
	 */
	public static function rectifyClassName($class_name, $file_path)
	{
		if (
			!@class_exists($class_name, false)
			&& !@trait_exists($class_name, false)
			&& !@interface_exists($class_name, false)
			&& is_file($file_path)
		) {
			$file = fopen($file_path, "r");
			do {
				$buffer = fgets($file);
				if (substr($buffer, 0, 10) === "namespace ") {
					$class_name = trim(substr($buffer, 10, strpos($buffer, ";") - 10))
						. "\\" . Namespaces::shortClassName($class_name);
					break;
				}
			} while (!feof($file));
			fclose($file);
		}
		return $class_name;
	}

}
