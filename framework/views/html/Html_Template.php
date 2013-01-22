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
		$params = $objects ? array_merge(array($this), array($objects)) : array();
		if ($i = strpos($func_call, "(")) {
			$func_name = substr($func_call, 0, $i);
			$i++;
			$j = strpos($func_call, ")", $i);
			$more_params = $this->parseFuncParams(substr($func_call, $i, $j - $i), $objects);
			$params = array_merge($params, $more_params);
			return call_user_func_array(array($object_call, $func_name), $params);
		}
		else {
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
		if (!isset($path)) {
			$path = str_replace("\\", "/", stream_resolve_include_path($css . "/style.css"));
			if ($i = strrpos($path, "/")) {
				$path = substr($path, 0, $i);
			}
			$path = Paths::$uri_root . substr($path, strlen(Paths::$file_root));
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
	 * @return string html content of the parsed page
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

	//----------------------------------------------------------------------------- parseArrayElement
	/**
	 * @param multitype:mixed $objects
	 * @param array $array
	 * @param string|integer $index
	 * @return mixed
	 */
	protected function parseArrayElement($objects, $array, $index)
	{
echo "parse array element $index from " . print_r($array, true) . "</pre>";
		return isset($array[$index]) ? $array[$index] : null;
	}

	//-------------------------------------------------------------------------------- parseClassName
	/**
	 * @param multitype:mixed $objects
	 * @param string $class_name
	 * @return string
	 */
	protected function parseClassName($objects, $class_name)
	{
		return Namespaces::fullClassName($class_name);
	}

	//------------------------------------------------------------------------------- parseCollection
	/**
	 * Parse a collection of objects
	 *
	 * @param Reflection_Property $property
	 * @param multitype:object $value
	 */
	protected function parseCollection(Reflection_Property $property, $collection)
	{
		return (new Html_Builder_Collection($property, $collection))->build();
	}

	//------------------------------------------------------------------------------------ parseConst
	/**
	 * Parse a constant and returns its return value
	 *
	 * @param multitype:object $objects
	 * @param mixed $object
	 * @param string $const_name
	 * @return mixed the value of the constant
	 */
	protected function parseConst($objects, $object, $const_name)
	{
		return (is_array($object) && isset($object[$const_name])) ? $object[$const_name] : (
			isset($GLOBALS[$const_name]) ? $GLOBALS[$const_name] : (
			isset($GLOBALS["_" . $const_name]) ? $GLOBALS["_" . $const_name] : null
		));
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param string $content
	 * @return string updated content
	 */
	protected function parseContainer($content)
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
	 * @return mixed
	 */
	protected function parseFunc($objects, $func_name)
	{
		return $this->callFunc(
			"SAF\\Framework\\Html_Template_Funcs",
			Names::propertyToMethod($func_name, "get"),
			$objects
		);
	}

	//------------------------------------------------------------------------------- parseFuncParams
	/**
	 * Parse a list of function parameters, separated by ","
	 *
	 * Accept quoted "constants" and 'constants'
	 * All other parameters values will be parsed as values
	 *
	 * @param string $params_string
	 * @param multitype:mixed $objects
	 * @return mixed
	 */
	protected function parseFuncParams($params_string, $objects)
	{
		$params = explode(",", $params_string);
		foreach ($params as $key => $param) {
			if (
					((substr($param, 0, 1) == '"') && (substr($param, -1) == '"'))
					|| ((substr($param, 0, 1) == "'") && (substr($param, -1) == "'"))
			) {
				$params[$key] = substr($param, 1, -1);
			}
			else {
				$params[$key] = $this->parseValue($objects, $param);
			}
		}
		return $params;
	}

	//---------------------------------------------------------------------------------- parseInclude
	/**
	 * Parse included view controller call result (must be an html view)
	 *
	 * @param string $include_uri
	 * @return string included template, parsed
	 */
	protected function parseInclude($include_uri)
	{
		ob_start();
		Main_Controller::getInstance()->runController($include_uri, array("is_included" => true));
		return ob_get_clean();
	}

	//------------------------------------------------------------------------------------- parseLoop
	private function parseLoop(&$content, $objects, $i, $j)
	{
		$var_name = substr($content, $i, $j - $i);
		$length = strlen($var_name);
		$i += $length + 3;
		$force_condition = (substr($var_name, -1) == "?");
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
		if ($force_condition) {
			$var_name = substr($var_name, 0, -1);
		}
		$loop_content = substr($content, $i, $j - $i);
		$this->removeSample($loop_content);
		$separator = $this->parseSeparator($loop_content);
		$elements = $this->parseValue($objects, $var_name, false);
		if ($from && !is_numeric($from)) {
			array_unshift($objects, $elements);
			$from = $this->parseValue($objects, $from);
			array_shift($objects);
		}
		if ($to && !is_numeric($to)) {
			array_unshift($objects, $elements);
			$to = $this->parseValue($objects, $to);
			array_shift($objects);
		}
		if ((is_array($elements) && !$force_condition) || isset($expr)) {
			array_unshift($objects, $elements);
			$do = false;
			$loop_insert = "";
			$counter = 0;
			if (is_array($elements)) foreach ($elements as $element) {
				$counter++;
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
					$counter++;
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
		elseif (is_array($elements)) {
			$loop_insert = empty($elements) ? "" : $this->parseVars($loop_content, $objects);
		}
		elseif (is_object($elements)) {
			array_unshift($objects, $elements);
			$loop_insert = $this->parseVars($loop_content, $objects);
			array_shift($objects);
		}
		elseif (strlen($elements)) {
			$loop_insert = $this->parseVars($loop_content, $objects);
		}
		else {
			$loop_insert = "";
		}
		$content = substr($content, 0, $i - $length - 7)
			. $loop_insert
			. substr($content, $j + $length2 + 7);
		return $i;
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
		while (($icontent = strpos($content, "<!--", $icontent)) !== false) {
			$i = $icontent + 4;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "-->", $i);
				$i = $this->parseLoop($content, $objects, $i, $j);
			}
		}
		return $content;
	}

	//----------------------------------------------------------------------------------- parseMethod
	/**
	 * @param multitype:mixed $objects
	 * @param object $object
	 * @param string $property_name
	 */
	protected function parseMethod($objects, $object, $property_name)
	{
		return $object->$property_name();
	}

	//--------------------------------------------------------------------------- parseObjectToString
	/**
	 * @param multitype:mixed $objects
	 * @param mixed $object
	 * @param string $property_name
	 * @return string
	 */
	protected function parseObjectToString($objects, $object, $property_name)
	{
		return method_exists($object, "__toString") ? strval($object) : "";
	}

	//-------------------------------------------------------------------------------- parseParameter
	/**
	 * @param multitype:mixed $objects
	 * @param mixed $object
	 * @param string $parameter_name
	 * @return mixed
	 */
	protected function parseParameter($objects, $object, $parameter_name)
	{
		return isset($this->parameters[$parameter_name])
			? $this->parameters[$parameter_name]
			: "";
	}

	//----------------------------------------------------------------------------------- parseParent
	/**
	 * @param multitype:mixed $objects
	 * @return mixed
	 */
	protected function parseParent(&$objects)
	{
		array_shift($objects);
		return reset($objects);
	}

	//--------------------------------------------------------------------------------- parseProperty
	/**
	 * @param multitype:mixed $objects
	 * @param object $object
	 * @param string $property_name
	 */
	protected function parseProperty($objects, $object, $property_name)
	{
		return $object->$property_name;
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Remove <!--separator-->(...) code from a loop content, and returns the separator content
	 *
	 * @param string $content
	 * @return string the separator content
	 */
	protected function parseSeparator(&$content)
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

	//----------------------------------------------------------------------------- parseStaticMethod
	/**
	 * @param multitype:mixed $objects
	 * @param string $class_name
	 * @param string $method_name
	 * @return mixed
	 */
	protected function parseStaticMethod($objects, $class_name, $method_name)
	{
		return $class_name::$method_name();
	}

	//--------------------------------------------------------------------------- parseStaticProperty
	/**
	 * @param multitype:mixed $objects
	 * @param string $class_name
	 * @param string $method_name
	 * @return mixed
	 */
	protected function parseStaticProperty($objects, $class_name, $property_name)
	{
		return $class_name::$$property_name;
	}

	//----------------------------------------------------------------------------------- parseString
	/**
	 * @param multitype:mixed $objects
	 * @param string $object
	 * @param string $property_name
	 */
	protected function parseString($objects, $object, $property_name)
	{
		$object = new String($object);
		return method_exists($object, $property_name)
		? $this->parseStringMethod($objects, $object, $property_name)
		: $this->parseStringProperty($objects, $object, $property_name);
	}

	//----------------------------------------------------------------------------- parseStringMethod
	/**
	 * @param multitype:mixed $objects
	 * @param string $object
	 * @param string $property_name
	 */
	protected function parseStringMethod($objects, $object, $method_name)
	{
		return $object->$method_name();
	}

	//--------------------------------------------------------------------------- parseStringProperty
	/**
	 * @param multitype:mixed $objects
	 * @param string $object
	 * @param string $property_name
	 */
	protected function parseStringProperty($objects, $object, $property_name)
	{
		return $object->$property_name;
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
		return (($c >= "a") && ($c <= "z")) || (($c >= "A") && ($c <= "Z"))
			|| ($c == "@") || ($c == "/") || ($c == ".") || ($c == "?");
	}

	//------------------------------------------------------------------------------ parseSingleValue
	/**
	 *
	 * @param multitype:mixed $objects
	 * @param string $property_name
	 * @param string $class_name
	 */
	protected function parseSingleValue(&$objects, $object, &$class_name, $property_name)
	{
		$source_object = $object;
		if (!strlen($property_name)) {
			$object = $this->parseParent($objects);
		}
		elseif (isset($class_name)) {
			$object = method_exists($class_name, $property_name)
			? $this->parseStaticMethod($objects, $class_name, $property_name)
			: $this->parseStaticProperty($objects, $class_name, $property_name);
			$class_name = null;
		}
		elseif (($property_name[0] >= 'A') && ($property_name[0] <= 'Z')) {
			if (
					(strlen($property_name) > 1) && ($property_name[1] >= 'a') && ($property_name[1] <= 'z')
			) {
				$class_name = $this->parseClassName($objects, $property_name);
			}
			else {
				$object = $this->parseConst($objects, $object, $property_name);
			}
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
		elseif (is_array($object)) {
			$object = $this->parseArrayElement($objects, $object, $property_name);
		}
		elseif (!is_object($object) && !isset($this->parameters[$property_name])) {
			$object = $this->parseString($objects, $object, $property_name);
		}
		elseif (method_exists($object, $property_name)) {
			$object = $this->parseMethod($objects, $object, $property_name);
		}
		elseif (isset($object->$property_name)) {
			$object = $this->parseProperty($objects, $object, $property_name);
		}
		else {
			$object = $this->parseParameter($objects, $object, $property_name);
		}
		if (($source_object instanceof Reflection_Property) && ($property_name == "value")) {
			$object = (new Reflection_Property_View($source_object))->formatValue($object);
		}
		return $object;
	}

	//------------------------------------------------------------------------------------ parseValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param multitype:object $objects
	 * @param string $var_name can be an unique var or path.of.vars
	 * @param boolean $as_string if true, returned value will always be a string
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	protected function parseValue($objects, $var_name, $as_string = true)
	{
		if ($var_name == ".") {
			return reset($objects);
		}
		else {
			$class_name = null;
			$object = reset($objects);
			foreach (explode(".", $var_name) as $property_name) {
				$object = $this->parseSingleValue($objects, $object, $class_name, $property_name);
			}
		}
		if ($as_string && is_object($object)) {
			$object = $this->parseObjectToString($objects, $object, $property_name);
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- parseVar
	protected function parseVar(&$content, $objects, $i, $j)
	{
		$var_name = substr($content, $i, $j - $i);
		$auto_remove = $this->parseVarWillAutoremove($var_name);
		$value = $this->parseValue($objects, $var_name);
		if (is_array($value) && (reset($objects) instanceof Reflection_Property)) {
			$value = $this->parseCollection(reset($objects), $value);
		}
		$i--;
		if ($auto_remove && !strlen($value)) {
			$this->parseVarRemove($content, $i, $j);
		}
		$content = substr($content, 0, $i) . $value . substr($content, $j + 1);
		return $i;
	}

	//-------------------------------------------------------------------------------- parseVarRemove
	protected function parseVarRemove($content, &$i, &$j)
	{
		if (
			(($content[$i - 1] === "'") && ($content[$j + 1] === "'"))
			|| (($content[$i - 1] === '"') && ($content[$j + 1] === '"'))
		) {
			$i--;
			$j++;
		}
		while (($content[$i] != " ") && ($content[$i] != ",")) {
			if (($content[$i] == '"') || ($content[$i] == "'")) {
				while ($content[$j] != $content[$i]) {
					$j++;
				}
			}
			$i--;
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
	 * @return string updated content
	 */
	private function parseVars($content, $objects)
	{
		$content = $this->parseLoops($content, $objects);
		$i = 0;
		while (($i = strpos($content, "{", $i)) !== false) {
			$i++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "}", $i);
				$i = $this->parseVar($content, $objects, $i, $j);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------ parseVarWillAutoremove
	protected function parseVarWillAutoremove(&$var_name)
	{
		if ($var_name[0] === "?") {
			$var_name = substr($var_name, 1);
			$auto_remove = true;
		}
		else {
			$auto_remove = false;
		}
		return $auto_remove;
	}

	//---------------------------------------------------------------------------------- removeSample
	/**
	 * Remove <!--sample-->(...) code from loop content
	 *
	 * @param string $content
	 */
	private function removeSample(&$content)
	{
		$i = strrpos($content, "<!--sample-->");
		if ($i !== false) {
			if (strpos($content, "<!--", $i + 1) === false) {
				$content = substr($content, 0, $i);
			}
		}
	}

	//---------------------------------------------------------------------------------- replaceLinks
	/**
	 * Replace links with correct absolute paths into $content
	 *
	 * @param string $content
	 * @return string updated content
	 */
	protected function replaceLinks($content)
	{
		$links = array("action=", "href=", "location=");
		$quotes = array("'", '"');
		foreach ($links as $link) {
			foreach ($quotes as $quote) {
				$i = 0;
				while (($i = strpos($content, $link . $quote, $i)) !== false) {
					$i += strlen($link) + 1;
					$j = strpos($content, $quote, $i);
					if (substr($content, $i, 1) === "/") {
						$full_path = Paths::$uri_root . Paths::$script_name . substr($content, $i, $j - $i);
						$content = substr($content, 0, $i) . $full_path . substr($content, $j);
					}
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
	protected function replaceUris($content)
	{
		$links = array('@import "', 'src="');
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, '"', $i);
				$file_name = substr($content, $i, $j - $i);
				$file_name = substr($file_name, strrpos($file_name, "/") + 1);
				$file_path = null;
				if (substr($file_name, -4) == ".css") {
					$file_path = static::getCssPath($this->css) . "/" . $file_name;
					if (!file_exists(Paths::$file_root . $file_path)) {
						$file_path = null;
					}
				}
				if (!isset($file_path)) {
					$file_path = Paths::$uri_root . substr(
						stream_resolve_include_path($file_name), strlen(Paths::$file_root)
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
	/**
	 * Set template parameters
	 * <ul>
	 * <li>is_included (boolean) : true if template is included into a page
	 *   main html head and foot will not be loaded
	 * <li>as_widget (boolean) : true if template is to load as a widget
	 *   main html head and foot will not be loaded
	 * </ul>
	 *
	 * @param multitype:mixed $parameters key is parameter name
	 */
	public function setParameters($parameters)
	{
		if (isset($parameters["is_included"]) && $parameters["is_included"]) {
			$parameters["as_widget"] = true;
		}
		$this->parameters = $parameters;
	}

}
