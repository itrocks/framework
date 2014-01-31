<?php
namespace SAF\Framework;

use Serializable;

/**
 * The main controller is called to run the application, with the URI and get/postvars as parameters
 */
class Main_Controller
{

	//------------------------------------------------------------------------------- activatePlugins
	/**
	 * Activate all plugins (called at each script beginning, for already loaded classes only)
	 *
	 * @param $plugins array
	 * @param $core    boolean activate core plugins too
	 *        please set this to true only if not already been activated by registerPlugins
	 */
	private function activatePlugins($plugins, $core)
	{
		foreach ($plugins as $level => $sub_plugins) {
			if ($core || ($level != "core")) {
				foreach ($sub_plugins as $class_name => $plugin) {
					if (class_exists($class_name, false) && ($plugin instanceof Activable_Plugin)) {
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

	//--------------------------------------------------------------------------------- createSession
	/**
	 * @return array
	 */
	private function createSession()
	{
		$this->includesStart();
		$session = Session::current(new Session());
		$configuration = $this->loadConfiguration();

		unset($_SESSION["include_path"]);
		$this->setIncludePath($_SESSION, strtolower($configuration->getApplicationName()));

		$session->plugins = $configuration->getPlugins();
		$this->registerPlugins($session->plugins, $configuration);
		return $session->plugins;
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

	//-------------------------------------------------------------------------------- includesResume
	private function includesResume()
	{
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

		// Plugins
		/** @noinspection PhpIncludeInspection */
		include_once "framework/core/configuration/Plugin_Register.php";

		$this->includesResume();
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

	//-------------------------------------------------------------------------------- registerPlugin
	/**
	 * @param $class_name      string
	 * @param $plugin_register Plugin_Register
	 * @return Plugin
	 */
	private function registerPlugin($class_name, Plugin_Register $plugin_register)
	{
		$plugin_configuration = $plugin_register->getConfiguration();
		/** @var $plugin Plugin */
		if (is_a($class_name, 'SAF\Framework\Configurable', true)) {
			$plugin = Builder::create($class_name, array($plugin_configuration));
			if (!($plugin instanceof Serializable)) {
				/** @noinspection PhpUndefinedFieldInspection hidden property for serialization */
				$plugin->plugin_configuration = $plugin_configuration;
			}
		}
		else {
			$plugin = Builder::create($class_name);
		}
		if ($plugin instanceof Aop_Dealer) {
			$plugin_register->dealer = $plugin;
		}
		$plugin->register($plugin_register);
		if (($plugin_register->level == "core") && ($plugin instanceof Activable_Plugin)) {
			/** @var $plugin Activable_Plugin */
			$plugin->activate();
		}
		return $plugin;
	}

	//------------------------------------------------------------------------------- registerPlugins
	/**
	 * Register plugins into session (called only at session beginning)
	 *
	 * @param $plugins array
	 * @param $configuration Configuration
	 */
	private function registerPlugins(&$plugins, $configuration)
	{
		$plugin_register = new Plugin_Register();
		foreach ($plugins as $level => $sub_plugins) {
			$plugin_register->level = $level;
			foreach ($sub_plugins as $class_name => $plugin_configuration) {
				if (is_numeric($class_name)) {
					$class_name = $plugin_configuration;
					$plugin_configuration = array();
				}
				$plugin_register->setConfiguration($plugin_configuration);
				$plugin = $this->registerPlugin($class_name, $plugin_register);
				if ($plugin instanceof Autoloader) {
					/** @noinspection PhpUndefinedVariableInspection Will always be set when $new_session true */
					$this->createApplication($configuration);
				}
				$plugins[$level][$class_name] = $plugin;
			}
		}
	}

	//--------------------------------------------------------------------------------- resumeSession
	/**
	 * @return array
	 */
	private function resumeSession()
	{
		$this->includesResume();
		$session = Session::current();
		$session->activatePlugins("core");
		return $session->plugins;
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
		$this->includes();
		if (empty($_SESSION)) {
			session_start();
//echo "<pre>" . print_r($_SESSION, true) . "</pre>";
if (isset($_GET["X"])) $_SESSION = array();
		}
		$new_session = empty($_SESSION);
		$this->setIncludePath($_SESSION);
		$plugins = $new_session ? $this->createSession() : $this->resumeSession();
		$this->activatePlugins($plugins, !$new_session);
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
