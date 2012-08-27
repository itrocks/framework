<?php
require_once "Framework/application/Autoloader.php";
require_once "Framework/toolbox/string.php";

class Main_Controller
{

	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var Main_Controller
	 */
	private static $instance;

	//----------------------------------------------------------------------------------- __construct
	private function __construct()
	{
	}

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param string $uri
	 * @param array  $get
	 * @param array  $post
	 * @param array  $files
	 * @return mixed
	 */
	private function call($uri, $get, $post, $files)
	{
		// parse URI
		$uri = new Uri($uri, "output");
		$controller_class = $uri->getControllerName() . "_Controller";
		echo "try $controller_class<br>";
		if (@class_exists($controller_class)) {
			// call controller : only him will parse parameters and call view
			$parameters = $uri->getParameters();
		} else {
			// call default view (use built-in default controller)
			$controller_class = "Default_" . $uri->getFeature();
			$parameters = Main_Controller::parseParameters($uri->getParameters());
		}
		echo "call $controller_class::call with parameters " . print_r($parameters, true) . "<br>";
		$controller = new $controller_class();
		$controller->call($parameters, $post, $files);
	}

	//------------------------------------------------------------------------------- connectDataLink
	private function connectDataLink()
	{
		$dao_class_name = $this->config->getDaoClassName();
		Connected_Environment::setCurrent(new Connected_Environment(
			Dao::newInstance($this->config->getDaoClassName(), $this->config->getDao())
		));
	}

	//----------------------------------------------------------------------------------- connectUser
	private function connectUser($form)
	{
		if (isset($form["login"]) && isset($form["password"])) {
			// TODO generic login using config.php content
		}
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

	//--------------------------------------------------------------------------------- getParameters
	public static function getParameters($uri_parameters)
	{
		$parameters = array();
		foreach ($uri_parameters as $class_name => $identifier) {
			$parameters[$class_name] = Getter::getObject($identifier, $class_name);
		}
		return $parameters;
	}

	//----------------------------------------------------------------------------------- loadSession
	public function loadConfiguration($application)
	{
		session_start();
		if ($application) {
			$this->config = new Config();
			$this->config->setCurrent($application);
		} else {
			$this->config = new Config($_SESSION["config"]);
		}
	}

	//------------------------------------------------------------------------------------------- run
	public function run($uri, $get, $post, $files)
	{
		$this->dispatchParams($get, $post);
		$this->loadConfiguration($post["app"]);
		$this->connectDataLink();
		$this->connectUser($post);
		return $this->call($uri, $get, $post, $files);
	}

}
