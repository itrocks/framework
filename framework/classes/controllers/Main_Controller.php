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
	private function runController($uri, $get, $post, $files)
	{
		$uri = new Controller_Uri($uri, "output");
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
					break;
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
		if (isset($_SESSION["configuration"]) && isset($_SESSION["user"])) {
			Configuration::setCurrent($_SESSION["configuration"]);
			User::setCurrent($_SESSION["user"]);
		}
		else {
			$configurations = new Configurations();
			$configurations->load();
		}
		$configuration = Configuration::getCurrent();
		$dao_class_name = Namespaces::fullClassName($configuration->getDaoClassName());
		Dao::setDataLink(new $dao_class_name($configuration->getDao()));
		$view_class_name = Namespaces::fullClassName($configuration->getViewEngineClassName());
		View::setCurrent(new $view_class_name($configuration->getViewEngine()));
	}

}
