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
	 * Parse URI and call controller / view
	 *
	 * @param string $uri
	 * @param array  $get
	 * @param array  $post
	 * @param array  $files
	 */
	private function call($uri, $get, $post, $files)
	{
		// parse URI
		$uri = new Uri($uri, "output");
		$controller_class = $uri->getControllerName() . "_Controller";
		echo "try $controller_class<br>";
		if (@class_exists($controller_class)) {
			// call specific controller : it will parse parameters and call view
			$controller = new $controller_class();
			$parameters = $uri->getParameters();
		} elseif (@class_exists("Default_" . $uri->getFeature() . "_Controller")) {
			// call default feature controller : it will parse parameters and call view
			$controller_class = "Default_" . $uri->getFeature() . "_Controller";
			$controller = new $controller_class();
			$parameters = $uri->getParameters();
		} else {
			// call default feature view (simply use built-in default controller)
			$controller = new Default_Controller($uri->getFeature(), $this->config->getViewClass());
			$parameters = Main_Controller::parseParameters($uri->getParameters());
		}
		$controller->call($parameters, $post, $files);
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

	//------------------------------------------------------------------------------------------- run
	public function run($uri, $get, $post, $files)
	{
		$this->dispatchParams($get, $post);
		$this->sessionStart();
		$this->call($uri, $get, $post, $files);
	}

	//---------------------------------------------------------------------------------- initDefaults
	public function sessionStart()
	{
		session_start();
		if ($_SESSION["configuration"] && $_SESSION["user"]) {
			Configuration::setCurrent($_SESSION["configuration"]);
			User::setCurrent($_SESSION["user"]);
		} else {
			$configurations = new Configurations();
			$configurations->load();
		}
		$configuration = Configuration::getCurrent();
		$dao_class_name = $configuration->getDaoClassName();
		Dao::setDataLink(new $dao_class_name($configuration->getDao()));
		$view_class_name = $configuration->getViewClassName();
		View::setCurrent(new $view_class_name($configuration->getView()));
	}

}
