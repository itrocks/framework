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

	//----------------------------------------------------------------------------- createApplication
	/**
	 * Create the current application object
	 *
	 * @param $configuration
	 */
	private function createApplication(Configuration $configuration)
	{
		/** @noinspection PhpIncludeInspection */
		include_once strtolower($configuration->getApplicationName()) . "/Application.php";
		/** @var $application Application */
		$application = Builder::create($configuration->getApplicationClassName());
		Application::current($application);
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

	//-------------------------------------------------------------------------------------- includes
	private function includes()
	{
		// Include_Path
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/OS.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Include_Path.php";

		// Autoloader
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Autoloader.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Namespaces.php";

		// Functions includes
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Array.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/String.php";
	}

	//----------------------------------------------------------------------------- loadConfiguration
	/**
	 * Load configuration
	 *
	 * @return Configuration
	 */
	private function loadConfiguration()
	{
		$configuration = Session::current()->get('SAF\Framework\Configuration');
		if (!$configuration) {
			$script_name = $_SERVER["SCRIPT_NAME"];
			$configuration = (new Configurations())->load(
				substr($script_name, strrpos($script_name, '/') + 1)
			);
			$this->loadPlugins($configuration);
		}
		return $configuration;
	}

	//------------------------------------------------------------------------------- loadCorePlugins
	/**
	 * Load core plugins
	 *
	 * @param $configuration Configuration
	 */
	private function loadPlugins(Configuration $configuration)
	{
		foreach (
			array("core", "highest", "higher", "high", "normal", "low", "lower", "lowest") as $level
		) {
			foreach ($configuration->$level as $class_name => $plugin_configuration) {
				if (is_numeric($class_name)) {
					$class_name = $plugin_configuration;
					$plugin_configuration = array();
				}
				/** @var $plugin Plugin */
				$plugin = Builder::create($class_name);
				if (!isset($aop_dealer) && ($class_name == 'SAF\Framework\Aop_Dealer')) {
					/** @var $aop_dealer Aop_Dealer */
					$aop_dealer = $plugin;
				}
				$plugin->register($aop_dealer, $plugin_configuration);
			}
		}
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
		$this->sessionStart($get, $post);
		$configuration = $this->loadConfiguration();
		$this->createApplication($configuration);
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
		if (empty($_SESSION)) {
			session_start();
		}
		$this->includes();
		$this->setFrameworkIncludePath($_SESSION);
		$this->startAutoloader();
		Session::start();
		unset($get[session_name()]);
		unset($post[session_name()]);
	}

	//----------------------------------------------------------------------- setFrameworkIncludePath
	/**
	 * @param $session array
	 */
	private function setFrameworkIncludePath(&$session)
	{
		if (isset($session["include_path"])) {
			set_include_path($session["include_path"]);
		}
		else {
			$include_path = (new Include_Path("framework"))->getIncludePath();
			$session["include_path"] = $include_path;
			set_include_path($include_path);
		}
	}

	//------------------------------------------------------------------------------- startAutoloader
	private function startAutoloader()
	{
		(new Autoloader())->register(null, null);
	}

}
