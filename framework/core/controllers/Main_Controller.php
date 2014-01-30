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

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * Activate all plugins (called only at session beginning)
	 *
	 * @param $plugins array
	 */
	private function activatePlugins($plugins)
	{
		foreach ($plugins as $level => $sub_plugins) {
			if ($level != "core") {
				foreach ($sub_plugins as $class_name => $plugin) {
					if (
						(class_exists($class_name, false) || trait_exists($class_name, false))
						&& ($plugin instanceof Activable_Plugin)
					) {
						$plugin->activate();
					}
				}
			}
		}
	}

	//----------------------------------------------------------------------------- createApplication
	/**
	 * Create the current application object
	 *
	 * @param $configuration
	 */
	private function createApplication(Configuration $configuration)
	{
		$class_name = $configuration->getApplicationClassName();
		$name = strtolower($configuration->getApplicationName());
		/** @noinspection PhpIncludeInspection */
		include_once strtolower($name) . "/Application.php";
		/** @var $application Application */
		$application = Builder::create($class_name, $name);
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
		// Low level includes
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Array.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Current.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Current_With_Default.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/Namespaces.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/OS.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/String.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Activable_Plugin.php";

		// Include_Path
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Include_Path.php";

		// Session
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Session.php";
	}

	//---------------------------------------------------------------------------------- includeStart
	private function includesStart()
	{
		// Configuration
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Configuration.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Configurations.php";

		// Plugins
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin_Register.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/mappers/Builder.php";

		// Core plugins
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/aop/Aop_Dealer.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Autoloader.php";
	}

	//----------------------------------------------------------------------------- loadConfiguration
	/**
	 * Load configuration
	 *
	 * @return Configuration
	 */
	private function loadConfiguration()
	{
		$script_name = $_SERVER["SCRIPT_NAME"];
		$configuration = (new Configurations())->load(
			substr($script_name, strrpos($script_name, '/') + 1)
		);
		return $configuration;
	}

	//------------------------------------------------------------------------------- registerPlugins
	/**
	 * Register plugins into session (called only at session beginning)
	 *
	 * @param $plugins array
	 */
	private function registerPlugins(&$plugins)
	{
		$plugin_register = new Plugin_Register();
		foreach ($plugins as $level => $sub_plugins) {
			foreach ($sub_plugins as $class_name => $plugin_configuration) {
				if (is_numeric($class_name)) {
					unset($plugins[$level][$class_name]);
					$class_name = $plugin_configuration;
					$plugin_configuration = array();
				}
				/** @var $plugin Plugin */
				$plugin = Builder::create($class_name);
				$plugins[$level][$class_name] = $plugin;
				if (!isset($aop_dealer) && ($class_name == 'SAF\Framework\Aop_Dealer')) {
					$plugin_register->dealer = $plugin;
				}
				else {
					$plugin_register->setConfiguration($plugin_configuration);
				}
				$plugin->register($plugin_register);
				if (($level == "core") && ($plugin instanceof Activable_Plugin)) {
					/** @var $plugin Activable_Plugin */
					$plugin->activate();
				}
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
			$_SESSION = array();
		}
		$new_session = empty($_SESSION);
		$this->includes();
		$this->setIncludePath($_SESSION);
		if ($new_session) {
			$this->includesStart();

			$configuration = $this->loadConfiguration();
			unset($_SESSION["include_path"]);
			$this->setIncludePath($_SESSION, strtolower($configuration->getApplicationName()));
			$plugins = $configuration->getPlugins();
			$this->registerPlugins($plugins);

			$session = Session::current(new Session());
			$session->plugins = $plugins;

			/** @noinspection PhpUndefinedVariableInspection Will always be set when $new_session true */
			$this->createApplication($configuration);

		}
		else {
			$plugins = Session::current()->plugins;
		}

		// non-core plugins activation must be done after application has been created
		$this->activatePlugins($plugins);
		unset($get[session_name()]);
		unset($post[session_name()]);
	}

	//----------------------------------------------------------------------- setFrameworkIncludePath
	/**
	 * @param $session          array
	 * @param $application_name string
	 */
	private function setIncludePath(&$session, $application_name = "framework")
	{
		if (isset($session["include_path"])) {
			set_include_path($session["include_path"]);
		}
		else {
			$include_path = (new Include_Path($application_name))->getIncludePath();
			$session["include_path"] = $include_path;
			set_include_path($include_path);
		}
	}

}
