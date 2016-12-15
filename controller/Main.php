<?php
namespace ITRocks\Framework\Controller;

use Exception;
use ITRocks\Framework\AOP\Include_Filter;
use ITRocks\Framework\AOP\Weaver\IWeaver;
use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Configuration;
use ITRocks\Framework\Configuration\Configurations;
use ITRocks\Framework\Controller;
use ITRocks\Framework\Error_Handler\Handled_Error;
use ITRocks\Framework\Error_Handler\Report_Call_Stack_Error_Handler;
use ITRocks\Framework\IAutoloader;
use ITRocks\Framework\Include_Path;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper\Object_Not_Found_Exception;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Plugin\Activable;
use ITRocks\Framework\Plugin\Manager;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Tools\Set;
use ITRocks\Framework\Updater\Application_Updater;
use ITRocks\Framework\View\View_Exception;

/**
 * The main controller is called to run the application, with the URI and get/postvars as parameters
 */
class Main
{

	//-------------------------------------------------------------------------------------- $current
	/**
	 * @var Main
	 */
	public static $current;

	//---------------------------------------------------------------------------------- $redirection
	/**
	 * If set, replace original controller result with this new controller call.
	 * All processes of the original and replacement controller are executed.
	 * Only the replacement controller resulting view will be returned.
	 *
	 * This is set by redirect()
	 * This is used and reset at run's end
	 *
	 * @var string
	 */
	private $redirection;

	//-------------------------------------------------------------------------------------- $running
	/**
	 * true if the main controller is running.
	 * false if it is done (run was complete).
	 * This allows Session::serialize() to know if the program crashed or not, and throw the call
	 * stack
	 *
	 * @var boolean
	 */
	public $running = true;

	//----------------------------------------------------------------------------- $top_core_plugins
	/**
	 * @var Plugin[]
	 */
	private $top_core_plugins = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor
	 */
	public function __construct()
	{
		if (!isset(self::$current)) {
			self::$current = $this;
		}
	}

	//----------------------------------------------------------------------------- addTopCorePlugins
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
		$application_updater->autoUpdate($this);
	}

	//----------------------------------------------------------------------------- createApplication
	/**
	 * Create the current application object
	 *
	 * @param $configuration Configuration
	 */
	private function createApplication(Configuration $configuration)
	{
		/** @noinspection PhpParamsInspection Built class will always be an application */
		Application::current(Builder::create(
			$configuration->getApplicationClassName(), [$configuration->getApplicationName()]
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
		$controller = isA($controller, Controller::class)
			? Builder::create($controller)
			: $uri->parameters->getMainObject($uri->controller_name);
		if (class_exists($uri->controller_name)) {
			Loc::enterContext($uri->controller_name);
			$exit_context = true;
		}
		if ($controller instanceof Class_Controller) {
			$result = call_user_func_array([$controller, $method_name],
				[$uri->parameters, $post, $files, $uri->feature_name, $uri->controller_name]
			);
		}
		else {
			$result = call_user_func_array([$controller, $method_name],
				[$uri->parameters, $post, $files, $uri->controller_name, $uri->feature_name]
			);
		}
		if (isset($exit_context)) {
			Loc::exitContext();
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- getController
	/**
	 * @param $controller_name string the name of the data class which controller we are looking for
	 * @param $feature_name    string the feature which controller we are looking for
	 * @param $sub_feature     string if set, the sub feature controller is searched into the feature
	 *                         controller namespace
	 * @return callable
	 */
	public function getController($controller_name, $feature_name, $sub_feature = null)
	{
		if (isset($sub_feature)) {
			list($class, $method) = Getter::get(
				$controller_name, $feature_name, Names::methodToClass($sub_feature) . '_Controller', 'php'
			);
		}

		if (!isset($class)) {
			list($class, $method) = Getter::get($controller_name, $feature_name, 'Controller', 'php');
		}

		if (!isset($class)) {
			list($class, $method) = [Default_Controller::class, 'run'];
		}

		/** @noinspection PhpUndefinedVariableInspection if $class is set, then $method is set too */
		return [$class, $method];
	}

	//--------------------------------------------------------------------------------------- globals
	private function globals()
	{
		foreach (['D', 'F', 'X'] as $var) {
			if (isset($_GET[$var])) {
				$GLOBALS[$var] = $_GET[$var];
				unset($_GET[$var]);
			}
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
	 * @return Main $this
	 */
	public function init($includes = [])
	{
		$this->globals();
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
		$configurations = new Configurations();
		$config = $configurations->getConfigurationFileNameFromComposer();
		if (!isset($config)) {
			$config = isset($_SERVER['CONFIG'])
				? ($_SERVER['CONFIG'] . '/config.php')
				: substr($_SERVER['SCRIPT_NAME'], strrpos($_SERVER['SCRIPT_NAME'], SL) + 1);
		}
		return $configurations->load($config);
	}

	//-------------------------------------------------------------------------------------- redirect
	/**
	 * @param $uri string
	 */
	public function redirect($uri)
	{
		$this->redirection = $uri;
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
		if (!$configuration) {
			die('Bad Request');
		}

		unset($_SESSION['include_path']);
		$session->environment         = $configuration->environment;
		$session->temporary_directory = $configuration->temporary_directory;
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
	 * Run main controller for given uri, get, post and files vars coming from the web call
	 *
	 * @param $uri   string
	 * @param $get   array
	 * @param $post  array
	 * @param $files array
	 * @return mixed
	 */
	public function run($uri, $get, $post, $files)
	{
		$result = null;
		try {
			$this->sessionStart($get, $post);
			$this->applicationUpdate();
			$result = $this->runController($uri, $get, $post, $files);
			if (isset($this->redirection)) {
				$uri = $this->redirection;
				unset($this->redirection);
				$result = $this->run($uri, $get, $post, $files);
			}
		}
		catch (Exception $exception) {
			$handled_error = new Handled_Error(
				$exception->getCode(), $exception->getMessage(), $exception->getFile(),
				$exception->getLine(), []
				);
			$handler = new Report_Call_Stack_Error_Handler();
			$handler->trace = 'Exception stack trace:' . LF . $exception->getTraceAsString() . LF;
			$handler->handle($handled_error);
		}
		$this->running = false;
		return $result;
	}

	//--------------------------------------------------------------------------------- runController
	/**
	 * Parse URI and run matching controller
	 *
	 * @param $uri         string The URI which describes the called controller and its parameters
	 * @param $get         array Arguments sent by the caller
	 * @param $post        array Posted forms sent by the caller
	 * @param $files       array Files sent by the caller
	 * @param $sub_feature string If set, the sub-feature (used by controllers which call another one)
	 * @return mixed View data returned by the view the controller called
	 */
	public function runController($uri, $get = [], $post = [], $files = [], $sub_feature = null)
	{
		$uri = new Uri($uri, $get);
		$uri->controller_name = Builder::className($uri->controller_name);
		$parameters = clone $uri->parameters;
		try {
			$main_object = $parameters->getMainObject();
		}
		catch (Object_Not_Found_Exception $exception) {
			return '<div class="error">' . $exception->getMessage() . '</div>';
		}
		$controller_name = ($main_object instanceof Set)
			? $main_object->element_class_name
			: $uri->controller_name;
		list($class_name, $method_name) = $this->getController(
			$controller_name, $uri->feature_name, $sub_feature
		);
		try {
			return $this->executeController($class_name, $method_name, $uri, $post, $files);
		}
		catch (View_Exception $exception) {
			return $exception->view_result;
		}
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
			ini_set('session.cookie_path', Paths::$uri_base);
			if (isset($get['memory_limit'])) {
				ini_set('memory_limit', $get['memory_limit']);
				unset($get['memory_limit']);
			}
			if (isset($get['time_limit'])) {
				set_time_limit($get['time_limit']);
				unset($get['time_limit']);
			}
			session_start();
			if (isset($GLOBALS['X'])) {
				$_SESSION = [];
			}
		}
		$this->setIncludePath($_SESSION, Application::class);
		if (isset($_SESSION['session']) && isset($_SESSION['session']->plugins)) {
			$this->resumeSession();
		}
		else {
			$this->createSession();
		}
		if (!Application::current()) {
			$_SESSION = [];
			$this->createSession();
		}
		unset($get[session_name()]);
		unset($post[session_name()]);
	}

	//-------------------------------------------------------------------------------- setIncludePath
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
