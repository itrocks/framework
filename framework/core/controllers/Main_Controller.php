<?php
namespace SAF\Framework;

/**
 * The main controller is called to run the application, with the URI and get/postvars as parameters
 */
class Main_Controller
{

	//----------------------------------------------------------------------------- $top_core_plugins
	/**
	 * @var Plugin[]
	 */
	private $top_core_plugins = array();

	//--------------------------------------------------------------------------------- topCorePlugin
	/**
	 * Top core plugins are defined into bootstrap script (index.php) and are registered before
	 * any session opening
	 *
	 * @param $plugins Plugin[]
	 * @return Main_Controller
	 */
	public function addTopCorePlugins($plugins)
	{
		$this->includes();
		foreach ($plugins as $plugin) {
			$this->top_core_plugins[get_class($plugin)] = $plugin;
			if ($plugin instanceof Activable_Plugin) {
				$plugin->activate();
			}
		}
		return $this;
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

	//--------------------------------------------------------------------------------- createSession
	private function createSession()
	{
		$this->includesStart();
		$session = Session::current(new Session());
		$session->plugins = new Plugins_Manager();
		$session->plugins->setTopCorePlugins($this->top_core_plugins);
		$configuration = $this->loadConfiguration();

		unset($_SESSION["include_path"]);
		$this->setIncludePath($_SESSION, strtolower($configuration->getApplicationName()));
		$this->registerPlugins($session->plugins, $configuration);
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
		include_once "framework/core/toolbox/Runkit_Patch.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/toolbox/String.php";

		// Include_Path
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Include_Path.php";

		// Plugins manager
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Activable_Plugin.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Configurable.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugins_Manager.php";

		// Session
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Session.php";

		// Core plugins
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/Autoloader.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/aop/Aop_Dealer.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/mappers/Builder.php";
	}

	//---------------------------------------------------------------------------------- includeStart
	private function includesStart()
	{
		// Configuration
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Configuration.php";
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Configurations.php";

		// Plugins manager
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin_Register.php";
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
	 * @param $plugins       Plugins_Manager
	 * @param $configuration Configuration
	 */
	private function registerPlugins(Plugins_Manager $plugins, Configuration $configuration)
	{
		foreach ($configuration->getPlugins() as $level => $sub_plugins) {
			foreach ($sub_plugins as $class_name => $plugin_configuration) {
				$plugin = $plugins->register($class_name, $level, $plugin_configuration);
				if ($plugin instanceof Autoloader) {
					/** @noinspection PhpUndefinedVariableInspection Will always be set when $new_session true */
					$this->createApplication($configuration);
				}
			}
		}
	}

	//--------------------------------------------------------------------------------- resumeSession
	private function resumeSession()
	{
		$session = Session::current();
		$session->plugins->setTopCorePlugins($this->top_core_plugins);
		$session->plugins->activatePlugins("core");
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
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
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
		$this->includes();
		if (empty($_SESSION)) {
			session_start();
			//echo "<pre>" . print_r($_SESSION, true) . "</pre>";
			if (isset($_GET["X"])) $_SESSION = array();
		}
		$this->setIncludePath($_SESSION);
		if (isset($_SESSION["session"]) && isset($_SESSION["session"]->plugins)) {
			$this->resumeSession();
		}
		else {
			$this->createSession();
		}
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
