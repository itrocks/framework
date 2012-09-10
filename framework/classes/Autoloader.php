<?php
namespace SAF\Framework;

require_once "framework/Application.php";
require_once "framework/classes/Configuration.php";
require_once "framework/classes/mappers/Aop_Getter.php";
require_once "framework/classes/toolbox/Aop.php";
require_once "framework/classes/toolbox/Namespaces.php";

abstract class Autoloader
{

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
	 * Includes the php file that contains the given named class
	 *
	 * Will return the short class name, or null if the class file was not found.
	 *
	 * @todo   restrict to the class namespace's corresponding application, in order to enable inclusion of classes that have the same name in several namespaces / applications.
	 * @param  string $class class name (with or without namespace)
	 * @return string | null found class name (without namespace)
	 */
	public static function autoLoad($class_name)
	{
		$class_short_name = Namespaces::shortClassName($class_name);
		if (!@include_once("$class_short_name.php")) {
			if (!Autoloader::$initialized) {
				Autoloader::init();
				if (!@include_once("$class_short_name.php")) {
					return null;
				}
			}
			else {
				return null;
			}
		}
		// TODO this should be defined using AOP into Aop_Getter, but doesn't work with AOP-PHP 0.2.0
		Aop_Getter::registerPropertiesGetters(Namespaces::fullClassName($class_name));
		return $class_name;
	}

	//-------------------------------------------------------------------------- getOriginIncludePath
	/**
	 * Returns PHP origin include path
	 *
	 * @return string
	 */
	public static function getOriginIncludePath()
	{
		if (!Autoloader::$origin_include_path) {
			Autoloader::$origin_include_path = get_include_path();
		}
		return Autoloader::$origin_include_path;
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
			$configuration = Configuration::getCurrent();
			if (isset($configuration)) $application_name = $configuration->getApplicationName();
			if (!isset($application_name)) $application_name = "Framework";
			$include_path = join(":", Application::getSourceDirectories($application_name));
			$_SESSION["php_ini"]["include_path"] = Autoloader::getOriginIncludePath() . ":" . $include_path;
		}
		set_include_path($_SESSION["php_ini"]["include_path"]);
		Autoloader::$initialized = true;
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Register autoloader, always reset autoloader when current Configuration changes
	 */
	public static function register()
	{
		spl_autoload_register(array(__CLASS__, "autoLoad"));
		Aop::registerBefore(
			__NAMESPACE__ . "\\Configuration->setCurrent()",
			array(__CLASS__, "reset")
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
		Autoloader::$initialized = false;
		unset($_SESSION["php_ini"]["include_path"]);
		set_include_path(Autoloader::$origin_include_path);
	}

}

Autoloader::register();
