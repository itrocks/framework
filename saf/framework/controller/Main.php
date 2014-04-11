<?php
namespace SAF\Framework\Controller;

use SAF\Framework\AOP\Include_Filter;
use SAF\Framework\AOP\Weaver\IWeaver;
use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Configuration;
use SAF\Framework\Configuration\Configurations;
use SAF\Framework\IAutoloader;
use SAF\Framework\Include_Path;
use SAF\Framework\Plugin;
use SAF\Framework\Plugin\Activable;
use SAF\Framework\Plugin\Manager;
use SAF\Framework\Session;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Set;
use SAF\Framework\Updater\Application_Updater;
use SAF\Framework\Widget\Data_List\List_Controller;

/**
 * The main controller is called to run the application, with the URI and get/postvars as parameters
 */
class Main
{

	//----------------------------------------------------------------------------- $top_core_plugins
	/**
	 * @var Plugin[]
	 */
	private $top_core_plugins = [];

	//--------------------------------------------------------------------------------- topCorePlugin
	/**
	 * Top core plugins are defined into bootstrap script (index.php) and are registered before
	 * any session opening
	 *
	 * @param $plugins array
	 * @return Main
	 */
	public function addTopCorePlugins($plugins)
	{
		foreach ($plugins as $plugin) {
			$this->top_core_plugins[get_class($plugin)] = $plugin;
			if ($plugin instanceof Activable) {
				$plugin->activate();
			}
		}
		return $this;
	}

	//----------------------------------------------------------------------------- applicationUpdate
	/**
	 * Update application
	 */
	private function applicationUpdate()
	{
		/** @var $application_updater Application_Updater */
		$application_updater = Session::current()->plugins->get(Application_Updater::class);
		if ($application_updater->mustUpdate()) {
			$application_updater->update($this);
			$application_updater->done();
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
		/** @noinspection PhpParamsInspection Built class will always be an application */
		Application::current(Builder::create(
			$configuration->getApplicationClassName(),
			$configuration->getApplicationName()
		));
	}

	//--------------------------------------------------------------------------------- createSession
	private function createSession()
	{
		$this->resetSession(Session::current(new Session()));
	}

	//----------------------------------------------------------------------------- executeController
	/**
	 * @param $controller  string
	 * @param $method_name string
	 * @param $uri         Uri
	 * @param $post        array
	 * @param $files       array
	 * @return string
	 */
	private function executeController($controller, $method_name, $uri, $post, $files)
	{
		$controller = new $controller();
		$class_name = ($controller instanceof List_Controller)
			? Namespaces::fullClassName(Set::elementClassNameOf($uri->controller_name))
			: $uri->controller_name;
		if ($controller instanceof Class_Controller) {
			return call_user_func_array([$controller, $method_name],
				[$uri->parameters, $post, $files, $uri->feature_name, $class_name]
			);
		}
		else {
			return call_user_func_array([$controller, $method_name],
				[$uri->parameters, $post, $files, $class_name, $uri->feature_name]
			);
		}
	}

	//-------------------------------------------------------------------------------------- includes
	private function includes()
	{
		foreach (glob(__DIR__ . '/../functions/*.php') as $file_name) {
			/** @noinspection PhpIncludeInspection */
			include_once Include_Filter::file($file_name);
		}
	}

	//------------------------------------------------------------------------------------------ init
	/**
	 * Called by the bootstrap only : initialisation of the first main controller
	 *
	 * @param $includes string[]
	 * @return $this
	 */
	public function init($includes = [])
	{
		$this->includes();
		foreach ($includes as $include) {
			/** @noinspection PhpIncludeInspection */
			include_once $include;
		}
		return $this;
	}

	//----------------------------------------------------------------------------- loadConfiguration
	/**
	 * Load configuration
	 *
	 * @return Configuration
	 */
	private function loadConfiguration()
	{
		$script_name = $_SERVER['SCRIPT_NAME'];
		$configuration = (new Configurations())->load(
			substr($script_name, strrpos($script_name, SL) + 1)
		);
		return $configuration;
	}

	//------------------------------------------------------------------------------- registerPlugins
	/**
	 * Register plugins into session (called only at session beginning)
	 *
	 * @param $plugins       Manager
	 * @param $configuration Configuration
	 */
	private function registerPlugins(Manager $plugins, Configuration $configuration)
	{
		$must_register = [];
		foreach ($configuration->getPlugins() as $level => $sub_plugins) {
			foreach ($sub_plugins as $class_name => $plugin_configuration) {
				// registers and activates only when weaver is set
				$plugin = $plugins->register($class_name, $level, $plugin_configuration, isset($weaver));
				// weaver is set : registers and actives all previous plugins
				if ($plugin instanceof IWeaver) {
					$weaver = $plugin;
					foreach ($must_register as $register) {
						$plugins->register(
							$register['class_name'], $register['level'], $register['plugin_configuration']
						);
					}
					unset($must_register);
				}
				// weaver is not set : keep plugin definition for further registering and activation
				if (!isset($weaver)) {
					$must_register[] = [
						'plugin'               => $plugin,
						'class_name'           => $class_name,
						'level'                => $level,
						'plugin_configuration' => $plugin_configuration
					];
				}
				if ($plugin instanceof IAutoloader) {
					$this->createApplication($configuration);
				}
			}
		}
		if (isset($weaver)) {
			$weaver->saveJoinpoints(Application::current()->getCacheDir() . SL . 'weaver.php');
		}
	}

	//--------------------------------------------------------------------------- reloadConfiguration
	/**
	 * Reload global configuration and register plugins again
	 */
	/*
	public function reloadConfiguration()
	{
		$configuration = $this->loadConfiguration();
		unset($_SESSION['include_path']);
		$this->setIncludePath($_SESSION);
		$this->registerPlugins(Session::current()->plugins, $configuration);
	}
	*/

	//---------------------------------------------------------------------------------- resetSession
	/**
	 * Initialise a new session, or refresh existing session for update
	 *
	 * @param Session $session default is current session
	 */
	public function resetSession(Session $session = null)
	{
		if (!isset($session)) {
			$session = Session::current();
		}
		$session->plugins = new Manager();
		$session->plugins->addPlugins('top_core', $this->top_core_plugins);
		$configuration = $this->loadConfiguration();

		unset($_SESSION['include_path']);
		$this->setIncludePath($_SESSION, $configuration->getApplicationClassName());
		$this->registerPlugins($session->plugins, $configuration);
	}

	//--------------------------------------------------------------------------------- resumeSession
	private function resumeSession()
	{
		$plugins = Session::current()->plugins;
		$plugins->addPlugins('top_core', $this->top_core_plugins);
		$plugins->activatePlugins('core');
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
		$this->applicationUpdate();
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
	public function runController($uri, $get = [], $post = [], $files = [])
	{
		$uri = new Uri($uri, $get, 'edit', 'list');
		list($class_name, $method_name) = $this->getController(
			$uri->controller_name, $uri->feature_name
		);
		return $this->executeController($class_name, $method_name, $uri, $post, $files);
		/*
		foreach ($uri->getPossibleControllerCalls() as $key => $call) {
			list($controller, $method_name) = $call;
			if (@method_exists($controller, $method_name)) {
				if (isset($_GET['F'])) {
					echo 'Execute controller ' . $key . ' = ' . json_encode($call) . BR;
				}
				return $this->executeController($controller, $method_name, $uri, $post, $files);
			}
		}
		return null;
		*/
	}

	//--------------------------------------------------------------------------------- getController
	/**
	 * @param $controller_name string
	 * @param $feature_name    string
	 * @return array [$controller_class_name, $method_name]
	 */
	private function getController($controller_name, $feature_name)
	{
		$method = 'run';

		// ie : $feature_class = 'featureName' transformed into 'Feature_Name'
		$feature_class = Names::methodToClass($feature_name);

		// $classes : the controller class name and its parents
		// ['Vendor\Application\Module\Class_Name' => '\Module\Class_Name']
		$classes = [];
		$class_name = $controller_name;
		do {
			$classes[$class_name] = substr(
				$class_name, strpos($class_name, BS, strpos($class_name, BS) + 1)
			);
			$class_name = get_parent_class($class_name);
		} while ($class_name);

		// Looking for specific controller for each application
		$application_class = get_class(Application::current());
		do {
			$namespace = Namespaces::of($application_class);

			// for the controller class and its parents
			foreach ($classes as $short_class_name) {
				$class_name = $namespace . $short_class_name;
				$path = strtolower(str_replace(BS, SL, $class_name));
				if (file_exists($path . SL . $feature_class . '_Controller.php')) {
					$class = $class_name . BS . $feature_class . '_Controller';
					break 2;
				}
				if (file_exists($path . SL . strtolower($feature_class) . SL . 'Controller.php')) {
					$class = $class_name . BS . $feature_class . SL . 'Controller';
					break 2;
				}
				if (file_exists(Names::classToPath($class_name) . '_' . $feature_class . '_Controller.php')) {
					$class = $class_name . '_' . $feature_class . '_Controller';
					break 2;
				}
				if (
					file_exists($path . SL . 'Controller.php')
					&& method_exists($class_name . BS . 'Controller', 'run' . ucfirst($feature_name))
				) {
					$class = $class_name . BS . 'Controller';
					$method = 'run' . ucfirst($feature_name);
					break 2;
				}
			}

			// next application is the parent one
			if (substr($controller_name, 0, strlen($namespace)) == $namespace) break;
			$application_class = get_parent_class($application_class);
		} while ($application_class);

		// Looking for default controller for each application
		if (empty($class)) {
			$application_class = get_class(Application::current());
			do {
				// looking for default controller
				$path = strtolower(str_replace(BS, SL, $namespace));
				if (file_exists($path . SL . strtolower($feature_class) . SL . 'Controller.php')) {
					$class = $namespace . BS . $feature_class . BS . 'Controller';
					break;
				}
				if (file_exists($path . SL . 'widget' . SL . strtolower($feature_class) . SL . 'Controller.php')) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . BS . 'Controller';
				}
				if (file_exists($path . SL . 'widget' . SL . $feature_class . '_Controller.php')) {
					$class = $namespace . BS . 'Widget' . BS . $feature_class . '_Controller';
					break;
				}

				// next application is the parent one
				$application_class = get_parent_class($application_class);
			} while($application_class);

			// Looking for default controller for each application
			if (empty($class)) {
				$application_class = get_class(Application::current());
				do {
					if (file_exists($file = $path . SL . 'controller/Default_Controller.php')) {
						$class = $namespace . BS . 'Controller' . BS . 'Default_Controller';
						break;
					}
					$application_class = get_parent_class($application_class);
				} while ($application_class);
			}

		}

		if (isset($class)) {
			return [$class, $method];
		}

		return [$controller_name, $feature_name];
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
			if (isset($_GET['X'])) $_SESSION = [];
		}
		$this->setIncludePath($_SESSION, Application::class);
		if (isset($_SESSION['session']) && isset($_SESSION['session']->plugins)) {
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
	 * @param $session           array
	 * @param $application_class string
	 */
	private function setIncludePath(&$session, $application_class)
	{
		if (isset($session['include_path'])) {
			set_include_path($session['include_path']);
		}
		else {
			$include_path = (new Include_Path($application_class))->getIncludePath();
			$session['include_path'] = $include_path;
			set_include_path($include_path);
		}
	}

}
