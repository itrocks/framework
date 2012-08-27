<?php

class Uri
{

	/**
	 * @var string
	 */
	private $controller;

	/**
	 * @var string
	 */
	private $feature;

	/**
	 * @var integer[] indice is the last controller element name
	 */
	private $parameters;

	//----------------------------------------------------------------------------------------- parse
	public function __construct($uri, $default_feature = null)
	{
		$uri = $this->uriToArray($uri);
		if ($default_feature && is_numeric(end($uri))) {
			$uri[] = $default_feature;
		}
		$this->parse($uri);
	} 

	//----------------------------------------------------------------------------- getControllerName
	public function getControllerName()
	{
		return $this->controller;
	}

	//------------------------------------------------------------------------------------ getFeature
	public function getFeature()
	{
		return $this->feature;
	}

	//--------------------------------------------------------------------------------- getParameters
	public function getParameters()
	{
		return $this->parameters;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @example $uri = array("sales_order", 148, "form"
	 *          will result on controller "Sales_Order_Form"
	 *          with parameter "Sales_Order" = 148
	 * @param string $uri
	 */
	private function parse($uri)
	{
		$this->controller = "";
		$this->controller_elements = array();
		$this->parameters = array(); 
		$last_controller_element = "";
		foreach ($uri as $uri_element) {
			if (is_numeric($uri_element)) {
				$this->parameters[$last_controller_element] = $uri_element + 0;
			} else {
				if ($this->controller) $this->controller .= "_";
				$controller_element = str_replace(" ", "_", ucwords(str_replace("_", " ", $uri_element)));
				$this->controller .= $controller_element;
				$last_controller_element = $controller_element;
			}
		}
		$this->feature = $last_controller_element;
	}

	//------------------------------------------------------------------------------------ uriToArray
	private function uriToArray($uri)
	{
		$uri = explode("/", str_replace(",", "/", $uri));
		array_shift($uri);
		return $uri;
	}

}
