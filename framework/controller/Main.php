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
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Set;
use SAF\Framework\Updater\Application_Updater;
use SAF\Framework\Widget\List_\List_Controller;

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
		$class_name = $configuration->getApplicationClassName();
		$name = strtolower($configuration->getApplicationName());
		/** @noinspection PhpIncludeInspection */
		include_once Include_Filter::file(strtolower($name) . '/Application.php');
		/** @var $application Application */
		$application = Builder::create($class_name, $name);
		Application::current($application);
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
		foreach (glob('framework/functions/*.php') as $file_name) {
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
		$this->setIncludePath($_SESSION, strtolower($configuration->getApplicationName()));
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
		$this->setIncludePath($_SESSION, strtolower($configuration->getApplicationName()));
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
		$this->setIncludePath($_SESSION);
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
	 * @param $session          array
	 * @param $application_name string
	 */
	private function setIncludePath(&$session, $application_name = 'framework')
	{
		if (isset($session['include_path'])) {
			set_include_path($session['include_path']);
		}
		else {
			$include_path = (new Include_Path($application_name))->getIncludePath();
			$session['include_path'] = $include_path;
			set_include_path($include_path);
		}
	}

}
