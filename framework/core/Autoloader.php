<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/core/toolbox/Array.php";
require_once "framework/core/toolbox/String.php";

require_once "framework/core/toolbox/Plugin.php";

require_once "framework/Application.php";
require_once "framework/core/configuration/Configuration.php";
require_once "framework/core/toolbox/Aop.php";
require_once "framework/core/toolbox/Namespaces.php";

abstract class Autoloader implements Plugin
{

	//----------------------------------------------------------------------------- $included_classes
	/**
	 * Included classes list
	 *
	 * @var string[] keys are arbitrary numeric
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
	 * Will return the short class name, or null if the class file was not found.
	 *
	 * @todo restrict to the class namespace's corresponding application, in order to enable inclusion of classes that have the same name in several namespaces / applications.
	 *
	 * @param $class_name string class name (with or without namespace)
	 * @return string|null found class name (without namespace)
	 */
	public static function autoload($class_name)
	{
		$short_class_name = Namespaces::shortClassName($class_name);
		if (!isset(self::$included_classes[$short_class_name])) {
			if (!@include_once($short_class_name . ".php")) {
				if (!self::$initialized) {
					static::init();
					if (!@include_once($short_class_name . ".php")) {
						return null;
					}
				}
				else {
					return null;
				}
			}
			$class_name = Namespaces::fullClassName($short_class_name);
			self::$included_classes[$short_class_name] = $class_name;
			self::classLoadEvent($class_name);
		}
		return $short_class_name;
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

	//-------------------------------------------------------------------------------- classLoadEvent
	/**
	 * This event can be used as pointcut when a new class has been loaded
	 *
	 * @param string $class_name full class name, with namespace
	 */
	private static function classLoadEvent($class_name)
	{
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader, always reset autoloader when current Configuration changes
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, "autoload"));
		Aop::add("before",
			__NAMESPACE__ . "\\Configuration->current()",
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
	 * @param AopJoinpoint $joinpoint
	 */
	public static function resetOnCurrentConfigurationChange(AopJoinpoint $joinpoint)
	{
		if (count($joinpoint->getArguments())) {
			static::reset();
		}
	}

}
