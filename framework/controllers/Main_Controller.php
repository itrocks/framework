<?php
namespace SAF\Framework;

class Main_Controller
{

	/**
	 * @var Main_Controller
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//-------------------------------------------------------------------------------- dispatchParams
	private function dispatchParams($get, &$post)
	{
		if (isset($get["app"]))      $post["app"]      = $get["app"];
		if (isset($get["login"]))    $post["login"]    = $get["login"];
		if (isset($get["password"])) $post["password"] = $get["password"];
	}

	//----------------------------------------------------------------------------------- getInstance
	/**
	 * @return Main_Controller
	 */
	public static function getInstance()
	{
		if (!Main_Controller::$instance) {
			Main_Controller::$instance = new Main_Controller();
		}
		return Main_Controller::$instance;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * run main controller
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
						$uri->parameters, $post, $files, Namespaces::fullClassName($uri->controller_name), $uri->feature_name
					);
					break;
				}
			}
		}
	}

	//---------------------------------------------------------------------------------- initDefaults
	public function sessionStart()
	{
		session_start();
		if (isset($_SESSION["configuration"]) && isset($_SESSION["user"])) {
			Configuration::setCurrent($_SESSION["configuration"]);
			User::setCurrent($_SESSION["user"]);
		} else {
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
