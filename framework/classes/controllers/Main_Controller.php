<?php
namespace SAF\Framework;

require_once "framework/Application.php";
require_once "framework/classes/Configuration.php";
require_once "framework/classes/toolbox/Namespaces.php";
require_once "framework/dao/Dao.php";
require_once "framework/views/View.php";

class Main_Controller
{

	//----------------------------------------------------------------------------------- __construct
	private function __construct() {}

	//-------------------------------------------------------------------------------- dispatchParams
	/**
	 * Dispatch some get params to post (login, password and app all already awaited as post vars)
	 *
	 * get params are added to $post, and removed from $get
	 *
	 * @param array $get
	 * @param array $post
	 */
	private function dispatchParams(&$get, &$post)
	{
		if (isset($get["app"])) {
			$post["app"] = $get["app"];
			unset($get["app"]);
		}
		if (isset($get["login"])) {
			$post["login"] = $get["login"];
			unset($get["login"]);
		}
		if (isset($get["password"])) {
			$post["password"] = $get["password"];
			unset($get["password"]);
		}
	}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * Get the Main_Controller instance
	 *
	 * @return Main_Controller
	 */
	public static function getInstance()
	{
		static $instance = null;
		if (!isset($instance)) {
			$instance = new Main_Controller();
		}
		return $instance;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run main controller for given uri, get, post and files vars comming from the web call
	 *
	 * @param string $uri
	 * @param array  $get
	 * @param array  $post
	 * @param array  $files
	 */
	public function run($uri, $get, $post, $files)
	{
		$this->dispatchParams($get, $post);
		$this->sessionStart();
		$this->runController($uri, $get, $post, $files);
	}

	//--------------------------------------------------------------------------------- runController
	/**
	 * Parse URI and run matching controller
	 *
	 * @param string $uri
	 * @param array  $get
	 * @param array  $post
	 * @param array  $files
	 */
	public function runController($uri, $get = array(), $post = array(), $files = array())
	{
		$uri = new Controller_Uri($uri, $get, "output", "list");
		foreach ($uri->getPossibleControllerCalls() as $call) {
			list($controller_class_name, $method_name) = $call;
			foreach (Application::getNamespaces() as $namespace) {
				$controller = $namespace . "\\" . $controller_class_name;
				if (@method_exists($controller, $method_name)) {
					$controller = new $controller();
					$controller->$method_name(
						$uri->parameters, $post, $files,
						Namespaces::fullClassName($uri->controller_name), $uri->feature_name
					);
					break 2;
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- sessionStart
	/**
	 * Start PHP session and reload already existing session parameters
	 */
	private function sessionStart()
	{
		session_start();
		if (isset($_SESSION["Configuration"])) {
			foreach ($_SESSION as $class_name => $value) {
				$class_name::current($value);
			}
		} else {
			$configurations = new Configurations();
			$configurations->load();
		}
		foreach (
			Configuration::current()->getClassesConfigurations() as $class_name => $configuration
		) {
			$class_name = Namespaces::fullClassName($class_name);
			$configuration_class_name = isset($configuration["class"])
				? Namespaces::fullClassName($configuration["class"])
				: $class_name;
			$class_name::current(new $configuration_class_name($configuration));
		}
	}

}
