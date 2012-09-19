<?php
namespace SAF\Framework;

class Html_Template
{

	//------------------------------------------------------------------------------------ $as_widget
	/**
	 * Display template as a widget
	 *
	 * @var boolean
	 */
	private $as_widget;

	//-------------------------------------------------------------------------------------- $content
	/**
	 * Content of the template file, changed by calculated result HTML content during parse()
	 *
	 * @var string
	 */
	private $content;

	//------------------------------------------------------------------------------------------ $css
	/**
	 * Css files relative directory (ie "default")
	 *
	 * @var string
	 */
	private $css;

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Feature name (name of a controller's method, end of the view name)
	 *
	 * @var string
	 */
	private $feature;

	//---------------------------------------------------------------------------------- $is_included
	/**
	 * Template is included into another one
	 *
	 * If true, links and URIs will not be parsed, as this will be done by the container 
	 *
	 * @var boolean
	 */
	private $is_included;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Template file path (base for css / javascript links)
	 *
	 * @var string
	 */
	private $path;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a template object, initializing the source data object and the template access path
	 *
	 * @param object $object
	 * @param string $template_file full path to template file
	 * @param string $feature_name feature name
	 */
	public function __construct($object, $template_file, $feature_name)
	{
		$this->object  = $object;
		$this->path    = substr($template_file, 0, strrpos($template_file, "/"));
		$this->content = file_get_contents($template_file);
		$this->feature = $feature_name;
	}

	//-------------------------------------------------------------------------------------- asWidget
	/**
	 * Template will be output as a widget
	 *
	 * This means that in <!--BEGIN-->...<!--END--> case, main HTML template will NOT be loaded.
	 * Use this to include templates in others, or for ajax calls.
	 *
	 * @param boolean $output_template_as_widget
	 */
	public function asWidget($output_template_as_widget = true)
	{
		$this->as_widget = $output_template_as_widget;
	}

	//-------------------------------------------------------------------------------------- callFunc
	/**
	 * Calls a function an returns result
	 *
	 * @param mixed  $object    object or class name
	 * @param string $func_call "functionName(param1value,param2value,...)" or "functionName"
	 */
	public function callFunc($object_call, $func_call, $object = null)
	{
		$params = $object ? array($this, $object) : array();
		if ($i = strpos($func_call, "(")) {
			$func_name = substr($func_call, 0, $i);
			$i ++;
			$j = strpos($func_call, ")", $i);
			$params = array_merge(
				split(",", substr($func_call, $i, $j - $i)),
				$params
			);
			return call_user_func_array(array($object_call, $func_name), $params);
		} else {
			return call_user_func_array(array($object_call, $func_call), $params);
		}
	}

	//------------------------------------------------------------------------------------ getCssPath
	/**
	 * @param string $css
	 * @return string
	 */
	public static function getCssPath($css)
	{
		static $css_path = array();
		$path = isset($css_path[$css]) ? $css_path[$css] : null;
		if (!$path) {
			$path = str_replace("\\", "/", stream_resolve_include_path($css . "/style.css"));
			if ($i = strrpos($path, "/")) {
				$path = substr($path, 0, $i);
			}
			$path = $_SERVER["SAF_ROOT"] . substr($path, strlen(Application::getSafRootPath()));
			$css_path[$css] = $path;
		}
		return $path;
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * @return string
	 */
	public function getFeature()
	{
		return $this->feature;
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Gets the template top object
	 *
	 * @return object
	 */
	public function getObject()
	{
		return $this->object;
	}

	//------------------------------------------------------------------------------------ isIncluded
	/**
	 * Template is treated as as included template
	 *
	 * This means that links and URIs will not be parsed, as this will be done by the container.
	 * If template is included, it will automatically considered as a widget too.
	 *
	 * @param boolean $template_is_included
	 */
	public function isIncluded($template_is_included = true)
	{
		if ($template_is_included) {
			$this->asWidget();
		}
		$this->is_included = $template_is_included;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse the template replacing templating codes by object's properties and functions results
	 *
	 * @return string
	 */
	public function parse()
	{
		$content = $this->content;
		$content = $this->parseContainer($content);
		$content = $this->parseVars($content, $this->object);
		if (!$this->is_included) {
			$content = $this->replaceLinks($content);
			$content = $this->replaceUris($content);
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param  string $content
	 * @return string updated content
	 */
	private function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, "<!--END-->", $i);
			if ($this->as_widget) {
				$content = substr($content, $i, $j - $i);
			}
			else {
				$container = file_get_contents(Html_Configuration::$main_template, true);
				$content = str_replace("{@content}", substr($content, $i, $j - $i), $container);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFunc
	/**
	 * Parse a special data / function and returns its return value
	 *
	 * @param object $object
	 * @param string $func_name
	 */
	private function parseFunc($object, $func_name)
	{
		return $this->callFunc(
			"\\SAF\\Framework\\Html_Template_Funcs",
			Names::propertyToMethod($func_name, "get"),
			$object
		);
	}

	//---------------------------------------------------------------------------------- parseInclude
	private function parseInclude($object, $include_uri)
	{
		ob_start();
		Main_Controller::getInstance()->runController($include_uri, array("is_included" => true));
		return ob_get_clean();
	}

	//-------------------------------------------------------------------------------------- parseVar
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param  object $object
	 * @param  string $var_name
	 * @return string
	 */
	private function parseVar($object, $var_name)
	{
		foreach (explode(".", $var_name) as $property_name) {
			if ($property_name[0] === "@") {
				$object = $this->parseFunc($object, substr($property_name, 1));
			}
			elseif ($property_name[0] === "/") {
				$object = $this->parseInclude($object, $property_name);
			}
			elseif ($i = strpos($property_name, "(")) {
				$object = $this->callFunc($object, $property_name);
			}
			elseif (!is_object($object)) {
				$object = new String($object);
				$object = $object->$property_name();
			}
			elseif (method_exists($object, $property_name)) {
				$object = $object->$property_name();
			} else {
				$object = $object->$property_name;
			}
		}
		if (is_object($object)) {
			return method_exists($object, "__toString") ? strval($object) : "";
		}
		else {
			return $object;
		}
	}

	//------------------------------------------------------------------------------------- parseVars
	/**
	 * Parse all variables from the template
	 *
	 * @example parsed variables will have those forms :
	 *   simply display variable or function /method result value :
	 *     {variable_name}
	 *     {methodName()}
	 *     {object_property.sub-object_property}
	 *     {@html_template_function_name}
	 *   condition / loop on variable or function / method result :
	 *     <!--variable_name-->(...)<!--variable_name-->
	 *     <!--methodName()-->(...)<!--methodName()-->
	 *     <!--@function-->(...)<!--@function-->
	 *
	 * @param  string $content
	 * @param  object $object
	 * @return string
	 */
	private function parseVars($content, $object)
	{
		$content = $this->parseLoops($content, $object);
		$i = 0;
		while (($i = strpos($content, "{", $i)) !== false) {
			$i ++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "}", $i);
				$var_name = substr($content, $i, $j - $i);
				$value = $this->parseVar($object, $var_name);
				if (is_array($value)) {
					$value = "...";
				}
				$content = substr($content, 0, $i - 1) . $value . substr($content, $j + 1);
				$i --;
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------ parseLoops
	/**
	 * Parse all loops and conditions from the template
	 *
	 * @example parsed conditions will have those forms :
	 *   <!--variable_name-->(...)<!--variable_name-->
	 *   <!--methodName()-->(...)<!--methodName()-->
	 *   <!--@function-->(...)<!--@function-->
	 *
	 * @param  string $string
	 * @param  object $object
	 * @return string updated content
	 */
	private function parseLoops($content, $object)
	{
		$i = 0;
		while (($i = strpos($content, "<!--" , $i)) !== false) {
			$i += 4;
			$j = strpos($content, "-->", $i);
			if ($this->parseThis($content, $i)) {
				$var_name = substr($content, $i, $j - $i);
				$length = strlen($var_name);
				$i += $length + 3;
				$j = strpos($content, "<!--" . $var_name . "-->", $j);
				$loop_content = substr($content, $i, $j - $i);
				$this->removeSample($loop_content);
				$separator = $this->parseSeparator($loop_content);
				$elements = $this->parseVar($object, $var_name);
				if (is_array($elements)) {
					$do = false;
					$loop_insert = "";
					foreach ($elements as $element) {
						if ($do) $loop_insert .= $this->parseVars($separator, $element); else $do = true;
						$sub_content = $this->parseVars($loop_content, $element);
						$loop_insert .= $sub_content;
					}
				}
				elseif (strlen($elements)) {
					$loop_insert = $this->parseVars($loop_content, $object);
				}
				else {
					$loop_insert  = "";
				}
				$content = substr($content, 0, $i - $length - 7)
					. $loop_insert
					. substr($content, $j + $length + 7);
				$i -= 4;
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Remove <!--separator-->(...) code from a loop content, and returns the separator content
	 *
	 * @param  string $content
	 * @return string the separator content
	 */
	private function parseSeparator(&$content)
	{
		if (($k = strpos($content, "<!--separator-->")) !== false) {
			$separator = substr($content, $k + 16);
			$content = substr($content, 0, $k);
		}
		else {
			$separator = "";
		}
		return $separator;
	}

	//------------------------------------------------------------------------------------- parseThis
	/**
	 * Return true if the text at position $i of $content is a variable, function name, an include
	 *
	 * @param  string $content
	 * @param  integer $i
	 * @return boolean
	 */
	private function parseThis($content, $i)
	{
		$c = $content[$i];
		return (($c >= "a") && ($c <= "z")) || ($c == "@") || ($c == "/");		
	}

	//---------------------------------------------------------------------------------- removeSample
	/**
	 * Remove <!--sample-->(...) code from loop content
	 *
	 * @param string $content
	 */
	private function removeSample(&$content)
	{
		if (($i = strrpos($content, "<!--sample-->")) !== false) {
			$content = substr($content, 0, $i);
		}
	}

	//---------------------------------------------------------------------------------- replaceLinks
	/**
	 * Replace links with correct absolute paths into $content
	 *
	 * @param string $content
	 */
	private function replaceLinks($content)
	{
		$links = array('action="', 'href="');
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, '"', $i);
				if (substr($content, $i, 1) === "/") {
					$full_path = substr($_SERVER["PHP_SELF"], 0, strpos($_SERVER["PHP_SELF"], ".php"))
						. substr($content, $i, $j - $i);
					$content = substr($content, 0, $i) . $full_path . substr($content, $j);
				}
			}
		}
		return $content;
	}

	//----------------------------------------------------------------------------------- replaceUris
	/**
	 * Replace URIs with correct URIs paths into $content
	 *
	 * @param  string $content
	 * @return string updated content
	 */
	private function replaceUris($content)
	{
		$links = array('@import "', 'src="');
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, '"', $i);
				$file_name = substr($content, $i, $j - $i);
				$file_name = substr($file_name, strrpos($file_name, "/") + 1);
				if (substr($file_name, -4) == ".css") {
					$file_path = Html_Template::getCssPath($this->css) . "/" . $file_name;
				} elseif ($file_name == "dojo.js") {
					$file_path = $_SERVER["SAF_ROOT"] . "dojo/dojo/dojo.js";
				} else {
					$file_path = $_SERVER["SAF_ROOT"] . substr(
						stream_resolve_include_path($file_name), strlen(Application::getSafRootPath())
					);
				}
				$content = substr($content, 0, $i) . $file_path . substr($content, $j);
			}
		}
		return $content;
	}

	//---------------------------------------------------------------------------------------- setCss
	public function setCss($css)
	{
		$this->css = $css;
	}

}
