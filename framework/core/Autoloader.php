<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Array.php";
require_once "framework/core/toolbox/Namespaces.php";
require_once "framework/core/toolbox/Plugin.php";

/**
 * This is the core autoloader : it searches and load PHP scripts containing classes
 */
abstract class Autoloader implements Plugin
{

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public static function autoload($class_name)
	{
		self::includeClass($class_name);
	}

	//---------------------------------------------------------------------------------- includeClass
	/**
	 * @param $class_name string The class name
	 * @param $file_path  string The file path. If null, this will be automatically searched into include path
	 * @return string the file path, if class was included. null if not.
	 */
	public static function includeClass($class_name, $file_path = null)
	{
		if (!isset($file_path)) {
			$file_path = stream_resolve_include_path(Namespaces::shortClassName($class_name) . ".php");
		}
		if (Namespaces::checkFilePath($class_name, $file_path)) {
			if ($file_path) {
				/** @noinspection PhpIncludeInspection */
				include_once $file_path;
			}
			return $file_path;
		}
		else {
			return null;
		}
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

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registers autoloader
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, "autoload"));
	}

}
