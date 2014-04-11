<?php
namespace SAF\Framework\Controller;

use SAF\Framework\Application;

/**
 * The controller URI contains the controller name, feature, and additional parameters
 */
class Uri
{

	//------------------------------------------------------------------------------ $controller_name
	/**
	 * The controller name : concat of the two first parameters names, separated by '_'
	 *
	 * @var string
	 */
	public $controller_name;

	//--------------------------------------------------------------------------------- $feature_name
	/**
	 * The feature name (last text in the URI, ie 'output' for URI = '/Order/3/output')
	 *
	 * @var string
	 */
	public $feature_name;

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * The list of parameters sent to the controller
	 *
	 * @example URI is '/Order/3/Line/2/output', there will be two parameters : 'Order' with it's value 3, and 'Line' with it's value 2
	 * @var Parameters
	 */
	public $parameters;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build a new Controller_Uri object knowing the URI as a text
	 *
	 * @param $uri                        string ie '/Order/3/Line/2/output', or 'User/login'
	 * @param $get                        array
	 * @param $default_element_feature    string the default feature name, ie put 'output' for '/Order/3'
	 * @param $default_collection_feature string
	 */
	public function __construct(
		$uri, $get = [], $default_element_feature = null, $default_collection_feature = null
	) {
		$uri = self::uriToArray($uri);
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

	//------------------------------------------------------------------------------------ arrayToUri
	/**
	 * Transforms an array to an URI
	 *
	 * @param $array string[]
	 * @return string
	 */
	public static function arrayToUri($array)
	{
		return SL . join(SL, $array);
	}

	//----------------------------------------------------------------------------------- setDefaults
	private function setDefaults()
	{
		if (!$this->controller_name && !$this->feature_name) {
			$this->controller_name = get_class(Application::current());
			$this->feature_name = 'home';
		}
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
	 * @example $uri = ['order', 148, 'form') will result on controller 'Order_Form' with parameter 'Order' = 148
	 * @param $uri string[]
	 */
	private function parseUri($uri)
	{
		$this->feature_name = '';
		$this->parameters = new Parameters($this);
		$key = 0;
		foreach ($uri as $key => $controller_element) {
			if (ctype_lower($controller_element[0]) || is_numeric($controller_element)) {
				break;
			}
		}
		$this->controller_name = join(BS, array_slice($uri, 0, $key));
		$uri = array_splice($uri, $key);
		$this->feature_name = array_shift($uri);
		if (is_numeric($this->feature_name)) {
			$this->parameters->set($this->controller_name, intval($this->feature_name));
			$this->feature_name = array_shift($uri);
		}
		$controller_elements = [];
		foreach ($uri as $uri_element) {
			if (ctype_upper($uri_element[0])) {
				$controller_elements[] = $uri_element;
			}
			else {
				if (is_numeric($uri_element)) {
					$this->parameters->set(join(BS, $controller_elements), intval($uri_element));
				}
				else {
					if ($controller_elements) {
						$this->parameters->addValue(join(BS, $controller_elements));
					}
					$this->parameters->addValue($uri_element);
				}
				$controller_elements = [];
			}
		}
	}

	//------------------------------------------------------------------------------------ uriToArray
	/**
	 * Change a text URI into an array URI
	 *
	 * @example '/Order/148/form' will become ['Order', '148', 'form')
	 * @param $uri string
	 * @return string[]
	 */
	public static function uriToArray($uri)
	{
		$uri = explode(SL, str_replace(',', SL, $uri));
		array_shift($uri);
		if (end($uri) === '') array_pop($uri);
		return $uri;
	}

}
