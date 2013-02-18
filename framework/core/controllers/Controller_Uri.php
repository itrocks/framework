<?php
namespace SAF\Framework;

class Controller_Uri
{

	//------------------------------------------------------------------------------ $controller_name
	/**
	 * The controller name : concat of the two first parameters names, separated by "_"
	 *
	 * @var string
	 */
	public $controller_name;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * The feature name (last text in the URI, ie "output" for URI = "/Order/3/output")
	 *
	 * @var string
	 */
	public $feature_name;

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * The list of parameters sent to the controller
	 *
	 * @example URI is "/Order/3/Line/2/output", there will be two parameters : "Order" with it's value 3, and "Line" with it's value 2
	 * @var Controller_Parameters
	 */
	public $parameters;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build a new Controller_Uri object knowing the URI as a text
	 *
	 * @param $uri                        string ie "/Order/3/Line/2/output", or "User/login"
	 * @param $get                        array
	 * @param $default_element_feature    string the default feature name, ie put "output" for "/Order/3"
	 * @param $default_collection_feature string
	 */
	public function __construct(
		$uri, $get = array(), $default_element_feature = null, $default_collection_feature = null
	) {
		$uri = $this->uriToArray($uri);
		if (isset($default_element_feature) && is_numeric(end($uri))) {
			$uri[] = $default_element_feature;
		}
		if (isset($default_collection_feature) && count($uri) == 1) {
			$uri[] = $default_collection_feature;
		}
		$this->parseUri($uri);
		$this->parseGet($get);
		$this->setDefaults();
	}

	//----------------------------------------------------------------------------------- setDefaults
	private function setDefaults()
	{
		if (!$this->controller_name && !$this->feature_name) {
			$this->controller_name = Configuration::current()->getApplicationClassName();
			$this->feature_name = "home";
		}
	}

	//-------------------------------------------------------------------- getPossibleControllerCalls
	/**
	 * Get the list of possible controller calls, in order of priority, based on uri
	 * Each controller call is an array with as elements : class name, method name
	 *
	 * @example for the uri "/Order/12/Lines/subForm", the possible controller calls will be :
	 * - "Order_Lines_Sub_Form_Controller", "run"
	 * - "Order_Lines_Controller", "subForm"
	 * - "Default_Sub_Form_Controller", "run"
	 * - "Default_Controller", "subForm"
	 * - "Default_Controller", "run"
	 * @return string[]
	 */
	public function getPossibleControllerCalls()
	{
		$feature_name_for_method = $this->feature_name;
		$feature_name_for_class = Names::methodToClass($feature_name_for_method);
		$controller = $this->controller_name;
		$controller_root = Namespaces::shortClassName($this->controller_name);
		$controllers = array();
		$namespaces = Application::getNamespaces();
		while ($controller) {
			$controllers[] = array($controller . "_" . $feature_name_for_class . "_Controller", "run");
			$controllers[] = array($controller . "_Controller", $feature_name_for_method);
			$controller = get_parent_class($controller);
		}
		foreach ($namespaces as $namespace) {
			$controller = $namespace . "\\" . $controller_root;
			while ($controller) {
				$controllers[] = array($controller . "_" . $feature_name_for_class . "_Controller", "run");
				$controllers[] = array($controller . "_Controller", $feature_name_for_method);
				$controller = get_parent_class($controller);
			}
		}
		foreach ($namespaces as $namespace) {
			$controllers[] = array($namespace . "\\Default_" . $feature_name_for_class . "_Controller", "run");
			$controllers[] = array($namespace . "\\Default_Controller", $feature_name_for_method);
			$controllers[] = array($namespace . "\\Default_Controller", "run");
		}
		return $controllers;
	}

	//-------------------------------------------------------------------------------------- parseGet
	/**
	 * Parse get parameters array
	 *
	 * @param $get string[]
	 */
	private function parseGet($get)
	{
		foreach ($get as $key => $value) {
			if (is_numeric($key)) {
				$this->parameters->addValue($value);
			}
			else {
				$this->parameters->set($key, $value);
			}
		}
	}

	//-------------------------------------------------------------------------------------- parseUri
	/**
	 * Parse URI text elements to transform them into parameters, feature name and controller name
	 *
	 * @example $uri = array("order", 148, "form") will result on controller "Order_Form" with parameter "Order" = 148
	 * @param $uri string[]
	 */
	private function parseUri($uri)
	{
		$this->feature_name = "";
		$this->parameters = new Controller_Parameters();
		$last_controller_element = "";
		$has_numeric = false;
		foreach ($uri as $i => $uri_element) {
			if (is_numeric($uri_element)) {
				$uri[$i] = $uri_element + 0;
				$has_numeric = $i;
				break;
			}
		}
		if ($has_numeric) {
			$i = 0;
			$length = count($uri);
			$controller_elements = array();
			while (($i < $length) && ($i < 2) && !is_numeric($uri[$i])) {
				$last_controller_element = str_replace(" ", "_", ucwords(str_replace("_", " ", $uri[$i])));
				$controller_elements[] = $last_controller_element;
				$i++;
			}
			if (($i < $length) && is_numeric($uri[$i])) {
				$this->parameters->set($last_controller_element, $uri[$i] + 0);
				$last_controller_element = "";
				$i++;
				if (($i < $length) && !is_numeric($uri[$i])) {
					$this->feature_name = lcfirst($uri[$i]);
					$i++;
				}
			}
			if (!$this->feature_name) {
				$this->feature_name = lcfirst(array_pop($controller_elements));
				$last_controller_element = end($controller_elements);
			}
			if (($i >= $length) || !is_numeric($uri[$i])) {
				$last_controller_element = "";
			}
			$controller_name = join("_", $controller_elements);
			while ($i < $length) {
				if (is_numeric($uri[$i])) {
					if ($last_controller_element) {
						$this->parameters->set($last_controller_element, $uri[$i]);
						$last_controller_element = "";
					}
					else {
						$this->parameters->addValue($uri[$i]);
					}
				}
				else {
					if ($last_controller_element) {
						$this->parameters->addValue($last_controller_element);
					}
					$last_controller_element = $uri[$i];
				}
				$i++;
			}
			if ($last_controller_element) {
				$this->parameters->addValue($last_controller_element);
			}
		}
		else {
			$controller_name = str_replace(
				" ", "_", ucwords(str_replace("_", " ", array_shift($uri)))
			);
			$this->feature_name = lcfirst(array_shift($uri));
			foreach ($uri as $uri_element) {
				$this->parameters->addValue($uri_element);
			}
		}
		$this->controller_name = Namespaces::fullClassName($controller_name);
	}

	//------------------------------------------------------------------------------------ uriToArray
	/**
	 * Change a text URI into an array URI
	 *
	 * @example "/Order/148/form" will become array("Order", "148", "form")
	 * @param $uri string
	 * @return string[]
	 */
	private function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		if (end($uri) === "") array_pop($uri);
		return $uri;
	}

}
