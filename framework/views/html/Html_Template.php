<?php
namespace SAF\Framework;
use StdClass;

class Html_Template
{

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

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	private $object;

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var multitype:string
	 */
	private $parameters;

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

	//-------------------------------------------------------------------------------------- callFunc
	/**
	 * Calls a function an returns result
	 *
	 * @param multitype:object $objects objects stack
	 * @param string $func_call "functionName(param1value,param2value,...)" or "functionName"
	 */
	public function callFunc($object_call, $func_call, $objects = null)
	{
		$params = $objects ? array_merge(array($this), $objects) : array();
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
			$path = $_SERVER["SAF_ROOT"] . substr($path, strlen($_SERVER["SAF_PATH"]));
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

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * Gets parameter value
	 *
	 * @param string $parameter
	 * @return mixed
	 */
	public function getParameter($parameter)
	{
		return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : null;
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
		$content = $this->parseVars($content, array($this->object));
		if (!isset($this->parameters["is_included"]) || !$this->parameters["is_included"]) {
			$content = $this->replaceLinks($content);
			$content = $this->replaceUris($content);
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param string $content
	 * @return string updated content
	 */
	private function parseContainer($content)
	{
		$i = strpos($content, "<!--BEGIN-->");
		if ($i !== false) {
			$i += 12;
			$j = strrpos($content, "<!--END-->", $i);
			if (isset($this->parameters["as_widget"]) && $this->parameters["as_widget"]) {
				$content = substr($content, $i, $j - $i);
			}
			else {
				$file_name = Html_Configuration::$main_template;
				$container = file_get_contents($file_name, true);
				$content = str_replace("{@content}", substr($content, $i, $j - $i), $container);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFunc
	/**
	 * Parse a special data / function and returns its return value
	 *
	 * @param multitype:object $objects
	 * @param string $func_name
	 */
	private function parseFunc($objects, $func_name)
	{
		return $this->callFunc(
			"SAF\\Framework\\Html_Template_Funcs",
			Names::propertyToMethod($func_name, "get"),
			$objects
		);
	}

	//---------------------------------------------------------------------------------- parseInclude
	/**
	 * Parse included view controller call result (must be an html view)
	 *
	 * @param string $include_uri
	 * @return string
	 */
	private function parseInclude($include_uri)
	{
		ob_start();
		Main_Controller::getInstance()->runController($include_uri, array("is_included" => true));
		return ob_get_clean();
	}

	//-------------------------------------------------------------------------------------- parseVar
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param multitype:object $objects
	 * @param string $var_name
	 * @return string
	 */
	private function parseVar($objects, $var_name)
	{
		if ($var_name == ".") {
			return reset($objects);
		}
		else {
			$object = reset($objects);
			foreach (explode(".", $var_name) as $property_name) {
				if (!strlen($property_name)) {
					array_shift($objects);
					$object = reset($objects);
				}
				elseif ($property_name[0] === "@") {
					$object = $this->parseFunc($objects, substr($property_name, 1));
				}
				elseif ($property_name[0] === "/") {
					$object = $this->parseInclude($property_name);
				}
				elseif ($i = strpos($property_name, "(")) {
					$object = $this->callFunc($objects, $property_name);
				}
				elseif (!is_object($object)) {
					$object = new String($object);
					$object = method_exists($object, $property_name)
						? $object->$property_name()
						: $object->$property_name;
				}
				elseif (method_exists($object, $property_name)) {
					$object = $object->$property_name();
				}
				elseif (property_exists($object, $property_name)) {
					$object = $object->$property_name;
				}
				else {
					$object = isset($this->parameters[$property_name]) ? $this->parameters[$property_name] : "";
				}
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
	 * @param string $content
	 * @param object $object
	 * @return string
	 */
	private function parseVars($content, $objects)
	{
		$content = $this->parseLoops($content, $objects);
		$i = 0;
		while (($i = strpos($content, "{", $i)) !== false) {
			$i ++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "}", $i);
				$var_name = substr($content, $i, $j - $i);
				if ($var_name[0] === "?") {
					$var_name = substr($var_name, 1);
					$auto_remove = true;
				}
				else {
					$auto_remove = false;
				}
				$value = $this->parseVar($objects, $var_name);
				if (is_array($value)) {
					$value = "...";
				}
				$i --;
				if ($auto_remove && !strlen($value)) {
					if (
						(($content[$i - 1] === "'") && ($content[$j + 1] === "'"))
						|| (($content[$i - 1] === '"') && ($content[$j + 1] === '"'))
					) {
						$i --;
						$j ++;
					}
					while (($content[$i] != " ") && ($content[$i] != ",")) $i--;
				}
				$content = substr($content, 0, $i) . $value . substr($content, $j + 1);
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
	 * @param string $string
	 * @param multitype:object $objects
	 * @return string updated content
	 */
	private function parseLoops($content, $objects)
	{
		$icontent = 0;
		while (($icontent = strpos($content, "<!--" , $icontent)) !== false) {
			$i = $icontent + 4;
			$j = strpos($content, "-->", $i);
			if ($this->parseThis($content, $i)) {
				$var_name = substr($content, $i, $j - $i);
				$length = strlen($var_name);
				$i += $length + 3;
				if (strpos($var_name, ":")) {
					list($var_name, $expr) = explode(":", $var_name);
					if (strpos($expr, "-") !== false) {
						list($from, $to) = explode("-", $expr);
					}
					else {
						$from = $to = $expr;
					}
					$to = (($to == "") ? null : $to);
				}
				else {
					$expr = null;
					$from = 0;
					$to = null;
				}
				$length2 = isset($expr) ? strlen($var_name) : $length;
				$j = strpos($content, "<!--" . $var_name . "-->", $j + 3);
				$loop_content = substr($content, $i, $j - $i);
				$this->removeSample($loop_content);
				$separator = $this->parseSeparator($loop_content);
				$elements = $this->parseVar($objects, $var_name);
				if ($from && !is_numeric($from)) {
					array_unshift($objects, $elements);
					$from = $this->parseVar($objects, $from);
					array_shift($objects);
				}
				if ($to && !is_numeric($to)) {
					array_unshift($objects, $elements);
					$to = $this->parseVar($objects, $to);
					array_shift($objects);
				}
				if (is_array($elements) || isset($expr)) {
					array_unshift($objects, $elements);
					$do = false;
					$loop_insert = "";
					$counter = 0;
					if (is_array($elements)) foreach ($elements as $element) {
						$counter ++;
						if (isset($to) && ($counter > $to)) break;
						if ($counter >= $from) {
							array_unshift($objects, $element);
							if ($do) {
								$loop_insert .= $this->parseVars($separator, $objects);
							}
							else {
								$do = true;
							}
							$sub_content = $this->parseVars($loop_content, $objects);
							$loop_insert .= $sub_content;
							array_shift($objects);
						}
					}
					if (isset($to) && ($counter < $to)) {
						array_unshift($objects, new StdClass());
						while ($counter < $to) {
							$counter ++;
							if ($counter >= $from) {
								if ($do) {
									$loop_insert .= $this->parseVars($separator, $objects);
								}
								else {
									$do = true;
								}
								$sub_content = $this->parseVars($loop_content, $objects);
								$loop_insert .= $sub_content;
							}
						}
						array_shift($objects);
					}
					array_shift($objects);
				}
				elseif (strlen($elements)) {
					$loop_insert = $this->parseVars($loop_content, $objects);
				}
				else {
					$loop_insert  = "";
				}
				$content = substr($content, 0, $i - $length - 7)
					. $loop_insert
					. substr($content, $j + $length2 + 7);
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Remove <!--separator-->(...) code from a loop content, and returns the separator content
	 *
	 * @param string $content
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
	 * @param string $content
	 * @param integer $i
	 * @return boolean
	 */
	private function parseThis($content, $i)
	{
		$c = $content[$i];
		return (($c >= "a") && ($c <= "z")) || ($c == "@") || ($c == "/") || ($c == ".") || ($c == "?");		
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
	 * @param string $content
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
					$file_path = static::getCssPath($this->css) . "/" . $file_name;
				} elseif ($file_name == "dojo.js") {
					$file_path = $_SERVER["SAF_ROOT"] . "dojo/dojo/dojo.js";
				} else {
					$file_path = $_SERVER["SAF_ROOT"] . substr(
						stream_resolve_include_path($file_name), strlen($_SERVER["SAF_PATH"])
					);
				}
				$content = substr($content, 0, $i) . $file_path . substr($content, $j);
			}
		}
		return $content;
	}

	//---------------------------------------------------------------------------------------- setCss
	/**
	 * @param string $css
	 */
	public function setCss($css)
	{
		$this->css = $css;
	}

	//--------------------------------------------------------------------------------- setParameters
	public function setParameters($parameters)
	{
		if (isset($parameters["is_included"]) && $parameters["is_included"]) {
			$parameters["as_widget"] = true;
		}
		$this->parameters = $parameters;
	}

}
