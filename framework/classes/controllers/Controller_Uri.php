<?php
namespace SAF\Framework;

class Controller_Uri
{

	//------------------------------------------------------------------------------ $controller_name
	/**
	 * The controller name : concat of all parameters names, separated by "_"
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

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Build a new Controller_Uri object knowing the URI as a text
	 *
	 * @param string $uri ie "/Order/3/Line/2/output", or "User/login"
	 * @param string $default_feature the default feature name, ie put "output" for "/Order/3"
	 */
	public function __construct(
		$uri, $get, $default_element_feature = null, $default_collection_feature = null
	) {
		$uri = $this->uriToArray($uri);
		if (isset($default_element_feature) && is_numeric(end($uri))) {
			$uri[] = $default_element_feature;
		}
		if (isset($default_collection_feature) && count($uri) == 1) {
			$uri[] = $default_collection_feature;
		}
		$this->parse($uri);
		foreach ($get as $key => $value) {
			$this->parameters->set($key, $value);
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
	 * @return multitype:multitype:string
	 */
	public function getPossibleControllerCalls()
	{
		$controller_name = $this->controller_name;
		$feature_name_for_method = $this->feature_name;
		$feature_name_for_class = Names::methodToClass($feature_name_for_method);
		return array(
			array($controller_name . "_" . $feature_name_for_class . "_Controller", "run"),
			array($controller_name . "_Controller", $feature_name_for_method),
			array("Default_" . $feature_name_for_class . "_Controller", "run"),
			array("Default_Controller", $feature_name_for_method),
			array("Default_Controller", "run")
		);
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse URI text elements to transform them into parameters, feature name and controller name 
	 *
	 * @example $uri = array("order", 148, "form") will result on controller "Order_Form" with parameter "Order" = 148
	 * @param multitype:string $uri
	 */
	private function parse($uri)
	{
		$this->parameters = new Controller_Parameters();
		$controller_elements = array();
		$last_controller_element = "";
		foreach ($uri as $uri_element) {
			if (is_numeric($uri_element)) {
				$this->parameters->set($last_controller_element, $uri_element);
			}
			else {
				$controller_element = str_replace(" ", "_", ucwords(str_replace("_", " ", $uri_element)));
				$controller_elements[] = $controller_element;
				$last_controller_element = $controller_element;
			}
		}
		$this->feature_name = lcfirst(array_pop($controller_elements));
		$this->controller_name = join("_", $controller_elements);
	}

	//------------------------------------------------------------------------------------ uriToArray
	/**
	 * Change a text URI into an array URI 
	 *
	 * @example "/Order/148/form" will become array("Order", "148", "form")
	 * @param string $uri
	 * @return multitype:string
	 */
	private function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		if (end($uri) === "") array_pop($uri);
		return $uri;
	}

}
