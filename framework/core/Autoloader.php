<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Array.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Plugin.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Namespaces.php";

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
abstract class Autoloader implements Plugin
{

	//----------------------------------------------------------------------------- $included_classes
	/**
	 * Included classes list
	 *
	 * @var mixed[] keys are arbitrary numeric, value is false if class not found, or the class file path if class was found and included
	 */
	private static $included_classes = array();

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public static function autoload($class_name)
	{
		if (!isset(self::$included_classes[$class_name])) {
			if (strpos($class_name, "Print_Model") !== false) echo "include class $class_name<br>";
			$included_classes[$class_name] = self::includeClass($class_name);
		}
	}

	//---------------------------------------------------------------------------------- includeClass
	/**
	 * @param $class_name string The class name
	 * @param $file_path  string The file path. If null, this will be automatically searched into include path
	 * @return string|boolean The full path for the file if class file was included, false if not found
	 */
	public static function includeClass($class_name, $file_path = null)
	{
		if (!isset($file_path)) {
			$file_path = stream_resolve_include_path(Namespaces::shortClassName($class_name) . ".php");
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

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers autoloader
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, "autoload"));
	}

}
