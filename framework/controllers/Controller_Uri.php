<?php
namespace Framework;

class Controller_Uri
{

	/**
	 * @var string
	 */
	public $controller_name;

	/**
	 * @var string
	 */
	public $feature_name;

	/**
	 * @var Controller_Parameters
	 */
	public $parameters;

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @param string $uri
	 * @param string $default_feature
	 */
	public function __construct($uri, $default_feature = null)
	{
		$uri = $this->uriToArray($uri);
		if ($default_feature && is_numeric(end($uri))) {
			$uri[] = $default_feature;
		}
		$this->parse($uri);
	} 

	//-------------------------------------------------------------------- getPossibleControllerCalls
	/**
	 * Return a list of possible controller calls, in order of priority, based on uri
	 * Each controller call is an array with as elements : class name, method name
	 *
	 * @example for the uri /order/12/lines/subform, the possible controller calls will be :
	 *   - "Order_Lines_Subform_Controller", "run"
	 *   - "Order_Lines_Controller",         "subform"
	 *   - "Default_Subform_Controller",     "run"
	 *   - "Default_Controller",             "subform"
	 *   - "Default_Controller",             "run"
	 *
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
	 * @example $uri = array("sales_order", 148, "form"
	 *   will result on controller "Sales_Order_Form"
	 *   with parameter "Sales_Order" = 148
	 * @param string $uri
	 */
	private function parse($uri)
	{
		$this->parameters = new Controller_Parameters();
		$controller_elements = array();
		$last_controller_element = "";
		foreach ($uri as $uri_element) {
			if (is_numeric($uri_element)) {
				$this->parameters->set($last_controller_element, $uri_element);
			} else {
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
	 * @param  string $uri
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
