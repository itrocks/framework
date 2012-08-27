<?php
require_once "Framework/application/Application.php";

class Autoloader
{

	/**
	 * @var boolean
	 */
	private $initialized;

	/**
	 * @var Autoloader
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//-------------------------------------------------------------------------------------- autoLoad
	public function autoLoad($class)
	{
		if (!@include_once("$class.php")) {
			$this->init();
			include_once "$class.php";
		}
	}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * @return Autoloader
	 */
	public static function getInstance()
	{
		if (!Autoloader::$instance) {
			Autoloader::$instance = new Autoloader();
		}
		return Autoloader::$instance;
	}

	//-------------------------------------------------------------------------- getOriginIncludePath
	public function getOriginIncludePath()
	{
		static $origin_include_path = "";
		if (!$origin_include_path) {
			$origin_include_path = get_include_path();
			if (!$origin_include_path) {
				$origin_include_path = ".";
			}
		}
		return $origin_include_path;
	}

	//------------------------------------------------------------------------------------------ init
	public function init($force = false)
	{
		if (!$this->initialized) { 
			if ($force || !isset($_SESSION["php_ini"]["include_path"])) {
				$include_path = join(":", Application::getSourceDirectories("Demo"));
				$_SESSION["php_ini"]["include_path"] = $this->getOriginIncludePath() . ":" . $include_path;
			}
			set_include_path($_SESSION["php_ini"]["include_path"]);
			$this->initialized = true;
		}
	}

}

//-------------------------------------------------------------------------------------- __autoload
function __autoload($class)
{
	Autoloader::getInstance()->autoLoad($class);
}
