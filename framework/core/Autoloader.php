<?php
namespace SAF\Framework;
use AopJoinpoint;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Array.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/String.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Plugin.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/Application.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/configuration/Configuration.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Aop.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Namespaces.php";

abstract class Autoloader implements Plugin
{

	//----------------------------------------------------------------------------- $included_classes
	/**
	 * Included classes list
	 *
	 * @var mixed[] keys are arbitrary numeric, value is false if class not found, or the class file path if class was found and included
	 */
	private static $included_classes = array();

	//---------------------------------------------------------------------------------- $initialized
	/**
	 * This is true when Autoloader has been initialized first
	 *
	 * @var boolean
	 */
	private static $initialized = false;

	//-------------------------------------------------------------------------- $origin_include_path
	/**
	 * The original PHP include_path is kept here
	 *
	 * This is the base include_path when Autoloader initializes after a call to reset().
	 *
	 * @var string
	 */
	private static $origin_include_path;

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * Includes the php file that contains the given class (must contain namespace)
	 *
	 * @param $class_name string class name (with or without namespace)
	 */
	public static function autoload($class_name)
	{
		if (!isset(self::$included_classes[$class_name])) {
			if (!self::$initialized) {
				static::init();
			}
			$short_class_name = Namespaces::shortClassName($class_name);
			self::$included_classes[$class_name] = self::includeClass($short_class_name);
		}
	}

	//-------------------------------------------------------------------------- getOriginIncludePath
	/**
	 * Returns PHP origin include path
	 *
	 * @return string
	 */
	public static function getOriginIncludePath()
	{
		if (!self::$origin_include_path) {
			self::$origin_include_path = get_include_path();
		}
		return self::$origin_include_path;
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
			$file_path = stream_resolve_include_path($class_name . ".php");
		}
		if ($file_path) {
			include_once $file_path;
		}
		return $file_path;
	}

	//------------------------------------------------------------------------------ includeSeparator
	/**
	 * The include separator is ":" under unix/linux and ";" under windows systems
	 *
	 * @return string
	 */
	private static function includeSeparator()
	{
		return (PHP_OS === "WINNT") ? ";" : ":";
	}

	//------------------------------------------------------------------------------------------ init
	/**
	 * Initializes the full include path for all application directories (if not already done)
	 *
	 * Once done, fils will be searched from higher-level application to basis SAF Framework application.
	 * To force re-initialization, call reset() before init().
	 */
	public static function init()
	{
		if (!isset($_SESSION["php_ini"]["include_path"])) {
			$configuration = Configuration::current();
			if (isset($configuration)) {
				$application_name = $configuration->getApplicationName();
			}
			if (!isset($application_name)) {
				$application_name = "Framework";
			}
			$include_path = join(
				static::includeSeparator(), Application::getSourceDirectories($application_name)
			);
			$_SESSION["php_ini"]["include_path"] = static::getOriginIncludePath()
				. static::includeSeparator() . $include_path;
		}
		set_include_path($_SESSION["php_ini"]["include_path"]);
		self::$initialized = true;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader, always reset autoloader when current Configuration changes
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, "autoload"));
		Aop::add("before",
			'SAF\Framework\Configuration->current()',
			array(__CLASS__, "resetOnCurrentConfigurationChange")
		);
	}

	//----------------------------------------------------------------------------------------- reset
	/**
	 * Reset the include path for applications.
	 *
	 * This resets the php include path, init() will be automatically called the next time a not found class is searched with autoload().
	 */
	public static function reset()
	{
		self::$initialized = false;
		unset($_SESSION["php_ini"]["include_path"]);
		set_include_path(self::$origin_include_path);
	}

	//------------------------------------------------------------- resetOnCurrentConfigurationChange
	/**
	 * Reset the include path for applications if current configuration changes.
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function resetOnCurrentConfigurationChange(AopJoinpoint $joinpoint)
	{
		if (count($joinpoint->getArguments())) {
			static::reset();
		}
	}

}
