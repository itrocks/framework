<?php
namespace SAF\Framework;

/**
 * The main controller is called to run the application, with the URI and get/postvars as parameters
 */
class Main_Controller
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * You can't instantiate the Main_Controller with a constructor as this is a singleton
	 */
	private function __construct() {}

	//----------------------------------------------------------------------------- configurationLoad
	/**
	 * Load configuration
	 */
	private function configurationLoad()
	{
		if (!Session::current()->get('SAF\Framework\Configuration')) {
			$script_name = $_SERVER["SCRIPT_NAME"];
			(new Configurations())->load(substr($script_name, strrpos($script_name, '/') + 1));
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
	 * @param $uri string
	 * @param $get array
	 * @param $post array
	 * @param $files array
	 * @return mixed
	 */
	public function run($uri, $get, $post, $files)
	{
		$this->configurationLoad();
		$this->sessionStart($get, $post);
		return $this->runController($uri, $get, $post, $files);
	}

	//--------------------------------------------------------------------------------- runController
	/**
	 * Parse URI and run matching controller
	 *
	 * @param $uri string
	 * @param $get array
	 * @param $post array
	 * @param $files array
	 * @return mixed
	 */
	public function runController($uri, $get = array(), $post = array(), $files = array())
	{
		$uri = new Controller_Uri($uri, $get, "edit", "list");
		foreach ($uri->getPossibleControllerCalls() as $call) {
			list($controller, $method_name) = $call;
			if (@method_exists($controller, $method_name)) {
				$controller = new $controller();
				$class_name = ($controller instanceof List_Controller)
					? Namespaces::fullClassName(Set::elementClassNameOf($uri->controller_name))
					: $uri->controller_name;
				if ($controller instanceof Class_Controller) {
					return call_user_func_array(array($controller, $method_name), array(
						$uri->parameters, $post, $files, $uri->feature_name, $class_name
					));
				}
				else {
					return call_user_func_array(array($controller, $method_name), array(
						$uri->parameters, $post, $files, $class_name, $uri->feature_name
					));
				}
			}
		}
		return null;
	}

	//---------------------------------------------------------------------------------- sessionStart
	/**
	 * Start PHP session and remove session id from parameters (if set)
	 *
	 * @param $get array
	 * @param $post array
	 */
	private function sessionStart(&$get, &$post)
	{
		Session::start();
		unset($get[session_name()]);
		unset($post[session_name()]);
	}

}
