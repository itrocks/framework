<?php
require_once "framework/application/Application.php";

class Autoloader
{

	/**
	 * @var Autoloader
	 */
	private static $instance;

	private static $origin_include_path;

	//-------------------------------------------------------------------------------------- autoLoad
	public static function autoLoad($class)
	{
		if (!@include_once("$class.php")) {
			Autoloader::init();
			include_once "$class.php";
		}
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
	public static function init($force = false)
	{
		if ($force || !isset($_SESSION["php_ini"]["include_path"])) {
			$configuration = Configuration::getCurrent();
			if (isset($configuration)) $application_name = $configuration->getApplicationName();
			if (!isset($application_name)) $application_name = "Framework";
			$include_path = join(":", Application::getSourceDirectories($application_name));
			$_SESSION["php_ini"]["include_path"] = Autoloader::getOriginIncludePath() . ":" . $include_path;
		}
		set_include_path($_SESSION["php_ini"]["include_path"]);
	}

	//----------------------------------------------------------------------------------------- reset
	public static function reset()
	{
		unset($_SESSION["php_ini"]["include_path"]);
	}

}

//--------------------------------------------------------------------------------------------- aop
aop_add_before("Configuration->setCurrent()", "Autoloader::reset");

//-------------------------------------------------------------------------------------- __autoload
function __autoload($class)
{
	Autoloader::autoLoad($class);
}
