<?php
namespace SAF\Framework;

class Autoloader
{

	/**
	 * @var boolean
	 */
	private static $initialized = false;

	/**
	 * @var Autoloader
	 */
	private static $instance;

	/**
	 * @var string
	 */
	private static $origin_include_path;

	//-------------------------------------------------------------------------------------- autoLoad
	/**
	 * 
	 * @param  string $class class name (with or without namespace)
	 * @return string | null found class name (without namespace)
	 */
	public static function autoLoad($class_name)
	{
		$class_short_name = Namespaces::shortClassName($class_name);
		if (!@include_once("$class_short_name.php")) {
			if (!Autoloader::$initialized) {
				Autoloader::init();
				include_once "$class_short_name.php";
			} else {
				return null;
			}
		}
		Aop_Getter::registerPropertiesGetters($class_name);
		return $class_name;
	}

	//-------------------------------------------------------------------------- getOriginIncludePath
	public static function getOriginIncludePath()
	{
		if (!Autoloader::$origin_include_path) {
			Autoloader::$origin_include_path = get_include_path();
		}
		return Autoloader::$origin_include_path;
	}

	//------------------------------------------------------------------------------------------ init
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

	//----------------------------------------------------------------------------------------- reset
	public static function reset()
	{
		Autoloader::$initialized = false;
		unset($_SESSION["php_ini"]["include_path"]);
	}

}

spl_autoload_register("SAF\\Framework\\Autoloader::autoLoad");
Aop::registerBefore("SAF\\Framework\\Configuration->setCurrent()", "SAF\\Framework\\Autoloader::reset");
