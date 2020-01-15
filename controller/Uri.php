<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Application;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Paths;

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
	 * @example URI is '/Order/3/Line/2/output', there will be two parameters : 'Order' with its value 3, and 'Line' with its value 2
	 * @var Parameters
	 */
	public $parameters;

	//------------------------------------------------------------------------------------------ $uri
	/**
	 * The original uri given to __construct is kept here
	 *
	 * @var string
	 */
	public $uri;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Build a new Controller_Uri object knowing the URI as a text
	 *
	 * @param $uri string ie '/Order/3/Line/2/output', or 'User/login'
	 * @param $get array
	 */
	public function __construct($uri, array $get = [])
	{
		$this->uri = $uri;
		$uri       = self::uriToArray($uri);
		$this->parseUri($uri);
		$this->parseGet($get);
		$this->setDefaults();
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * Gets the URI back as string
	 */
	public function __toString()
	{
		return $this->uri;
	}

	//------------------------------------------------------------------------------------ arrayToUri
	/**
	 * Transforms an array to an URI
	 *
	 * @param $array string[]
	 * @return string
	 */
	public static function arrayToUri(array $array)
	{
		return SL . join(SL, $array);
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * @return string current URI string
	 */
	public static function current()
	{
		return isset($_SERVER['REQUEST_URI']) ?
			rParse($_SERVER['REQUEST_URI'], Paths::$uri_base)
			: null;
	}

	//----------------------------------------------------------------------------------- isClassName
	/**
	 * Returns true if the parameter is the name of a class
	 *
	 * @param $parameter string
	 * @return boolean
	 */
	public function isClassName($parameter)
	{
		return $parameter && ctype_upper($parameter[0]) && (strpos($parameter, DOT) === false);
	}

	//-------------------------------------------------------------------------------------- parseGet
	/**
	 * Parse get parameters array
	 *
	 * @param $get string[]
	 */
	private function parseGet(array $get)
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @example $uri = ['order', 148, 'form') will result on controller 'Order_Form' with parameter 'Order' = 148
	 * @param $uri string[]
	 */
	private function parseUri(array $uri)
	{
		// get main object = controller name
		$key                = 0;
		$controller_element = '';
		foreach ($uri as $key => $controller_element) {
			if (!$this->isClassName($controller_element)) {
				break;
			}
		}
		if ($this->isClassName($controller_element)) {
			$key++;
		}
		$this->controller_name = join(BS, array_slice($uri, 0, $key));
		$uri                   = array_splice($uri, $key);

		// get main object (as first parameter) and feature name
		$this->feature_name = array_shift($uri);
		$this->parameters   = new Parameters($this);
		if (is_numeric($this->feature_name)) {
			$this->parameters->set($this->controller_name, intval($this->feature_name));
			$this->feature_name = array_shift($uri);
			if (!$this->feature_name) {
				/** @noinspection PhpUnhandledExceptionInspection controller name must be valid */
				$reflection_class = new Reflection_Class($this->controller_name);
				$default_feature  = $reflection_class->getAnnotation('default_object_feature')->value
					?: $reflection_class->getAnnotation('default_feature')->value;
				$this->feature_name = $default_feature ?: Feature::F_OUTPUT;
			}
		}
		elseif ($this->controller_name && !$this->feature_name) {
			if (class_exists($this->controller_name)) {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				$reflection_class   = new Reflection_Class($this->controller_name);
				$default_feature    = $reflection_class->getAnnotation('default_class_feature')->value;
				$this->feature_name = $default_feature ?: Feature::F_ADD;
			}
			elseif (class_exists(Names::setToClass($this->controller_name, false))) {
				/** @noinspection PhpUnhandledExceptionInspection class_exists */
				$reflection_class   = new Reflection_Class(Names::setToClass($this->controller_name));
				$default_feature    = $reflection_class->getAnnotation('default_set_feature')->value;
				$this->feature_name = $default_feature ?: Feature::F_LIST;
			}
			else {
				$this->feature_name = Feature::F_DEFAULT;
			}
		}

		// get main parameters
		$controller_elements = [];
		foreach ($uri as $uri_element) {
			if ($this->isClassName($uri_element)) {
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
		if ($controller_elements) {
			$this->parameters->addValue(join(BS, $controller_elements));
		}

	}

	//-------------------------------------------------------------------------------------- previous
	/**
	 * Get previous uri
	 *
	 * @return string previous uri or default uri ('/')
	 */
	public static function previous()
	{
		$uri      = SL;
		$referrer = isset($_SERVER['HTTP_REFERER'])
			? rParse($_SERVER['HTTP_REFERER'], '//' . $_SERVER['SERVER_NAME'] . Paths::$uri_base)
			: null;
		if (
			$referrer
			&& ($referrer !== SL)
			&& (substr($referrer, -2) !== '?X')
			&& (substr($referrer, -4) !== '?X&Z')
		) {
			$uri = $referrer;
		}
		return $uri;
	}

	//----------------------------------------------------------------------------------- setDefaults
	private function setDefaults()
	{
		if (!$this->controller_name && !$this->feature_name) {
			$this->controller_name = get_class(Application::current());
			$this->feature_name    = 'home';
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
		if (end($uri) === '') {
			array_pop($uri);
		}
		return $uri;
	}

}
