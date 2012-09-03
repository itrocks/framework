<?php
namespace Framework;

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
		$i = strrpos($class, "\\");
		if ($i !== false) {
				$class = substr($class, $i + 1);
		}
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

spl_autoload_register("Framework\\Autoloader::autoLoad");
Aop::registerBefore("Framework\\Configuration->setCurrent()", "Framework\\Autoloader::reset");
