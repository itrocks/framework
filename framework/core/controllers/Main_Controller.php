<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/Application.php";
require_once "framework/core/configuration/Configuration.php";
require_once "framework/core/toolbox/Namespaces.php";
require_once "framework/dao/Dao.php";
require_once "framework/views/View.php";

class Main_Controller
{

	//----------------------------------------------------------------------------------- __construct
	private function __construct() {}

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
	 */
	public function run($uri, $get, $post, $files)
	{
		$this->sessionStart($get, $post);
		$this->runController($uri, $get, $post, $files);
	}

	//--------------------------------------------------------------------------------- runController
	/**
	 * Parse URI and run matching controller
	 *
	 * @param $uri string
	 * @param $get array
	 * @param $post array
	 * @param $files array
	 */
	public function runController($uri, $get = array(), $post = array(), $files = array())
	{
		$uri = new Controller_Uri($uri, $get, "output", "list");
		foreach ($uri->getPossibleControllerCalls() as $call) {
			list($controller, $method_name) = $call;
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

	//---------------------------------------------------------------------------------- sessionStart
	/**
	 * Start PHP session and reload already existing session parameters
	 *
	 * @param $get array
	 * @param $post array
	 */
	private function sessionStart(&$get, &$post)
	{
		$session = Session::start();
		unset($get[session_name()]);
		unset($post[session_name()]);
		foreach ($session->getAll() as $class_name => $value) {
			if (is_object($value)) {
				call_user_func(array($class_name, "current"), $value);
			}
		}
		if (!Configuration::current()) {
			$configurations = new Configurations();
			$configurations->load();
			$session->set(Configuration::current());
		}
	}

}
