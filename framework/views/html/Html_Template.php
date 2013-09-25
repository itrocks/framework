<?php
namespace SAF\Framework;

use StdClass;

/**
 * built-in SAF HTML template engine
 */
class Html_Template
{

	//-------------------------------------------------------------------------------------- $content
	/**
	 * Content of the template file, changed by calculated result HTML content during parse()
	 *
	 * @var string
	 */
	protected $content;

	//------------------------------------------------------------------------------------------ $css
	/**
	 * Css files relative directory (ie "default")
	 *
	 * @var string
	 */
	protected $css;

	//---------------------------------------------------------------------------------- $descendants
	/**
	 * Descendant objects are set when calls to parents are done, in order to get them back
	 *
	 * @var mixed[]
	 */
	protected $descendants = array();

	//---------------------------------------------------------------------------- $descendants_names
	/**
	 * Descendant objects names are set when calls to parents are done, in order to get them back
	 *
	 * @var mixed[]
	 */
	protected $descendants_names = array();

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Feature name (name of a controller's method, end of the view name)
	 *
	 * @var string
	 */
	protected $feature;

	//-------------------------------------------------------------------------------- $main_template
	/**
	 * The main template template
	 *
	 * @var string
	 */
	public $main_template = "Default_main.html";

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * The objects queue, updated during the parsing
	 *
	 * @var mixed[]
	 */
	public $objects = array();

	//----------------------------------------------------------------------------------- $parameters
	/**
	 * @var string[]
	 */
	protected $parameters;

	//----------------------------------------------------------------------------- $parse_class_name
	/**
	 * Currently parsing full class name
	 * For static calls like {User.current}
	 *
	 * @var string
	 */
	public $parse_class_name;

	//----------------------------------------------------------------------------------------- $path
	/**
	 * Template file path (base for css / javascript links)
	 *
	 * @var string
	 */
	protected $path;

	//------------------------------------------------------------------------------------- $preprops
	/**
	 * This prepare preprops for @edit calls : each loop adds the property name and value to $preprops
	 *
	 * @var string[]
	 */
	public $preprops = array();

	//------------------------------------------------------------------------------------ $var_names
	/**
	 * Var names
	 * Keys correspond to objects keys
	 *
	 * @var string[]
	 */
	public $var_names = array();

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a template object, initializing the source data object and the template access path
	 *
	 * @param $object object
	 * @param $template_file string full path to template file
	 * @param $feature_name string feature name
	 */
	public function __construct($object = null, $template_file = null, $feature_name = null)
	{
		if (isset($object)) {
			array_unshift($this->var_names, "root");
			array_unshift($this->objects, $object);
		}
		if (isset($template_file)) {
			$this->path    = substr($template_file, 0, strrpos($template_file, "/"));
			$this->content = file_get_contents($template_file);
		}
		if (isset($feature_name)) {
			$this->feature = $feature_name;
		}
	}

	//-------------------------------------------------------------------------------------- callFunc
	/**
	 * Calls a function and returns result
	 *
	 * @param $object_call object|string object or class name
	 * @param $func_call   string "functionName(param1value,param2value,...)" or "functionName"
	 * @return mixed
	 */
	public function callFunc($object_call, $func_call)
	{
		if ($i = strpos($func_call, "(")) {
			$func_name = substr($func_call, 0, $i);
			$i++;
			$j = strpos($func_call, ")", $i);
			$params = $this->parseFuncParams(substr($func_call, $i, $j - $i));
		}
		else {
			$func_name = $func_call;
			$params = array();
		}
		if (is_a($object_call, 'SAF\Framework\Html_Template_Functions', true)) {
			$params = array_merge(array($this), $params);
		}
		return call_user_func_array(array($object_call, $func_name), $params);
	}

	//--------------------------------------------------------------------------- getContainerContent
	/**
	 * @param $file_name string
	 * @return string
	 */
	protected function getContainerContent($file_name)
	{
		return file_get_contents($file_name, !strpos($this->main_template, "/"));
	}

	//------------------------------------------------------------------------------------ getCssPath
	/**
	 * @param $css string
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
			$path = substr($path, strlen(Paths::$file_root));
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
		return reset($this->objects);
	}

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * Gets parameter value
	 *
	 * @param $parameter string
	 * @return mixed
	 */
	public function getParameter($parameter)
	{
		return isset($this->parameters[$parameter]) ? $this->parameters[$parameter] : null;
	}

	//------------------------------------------------------------------------------- getParentObject
	/**
	 * Gets the first parent of current (last) object that is an object
	 *
	 * @param $instance_of string class name
	 * @return object
	 */
	public function getParentObject($instance_of = null)
	{
		$object = null;
		if (reset($this->objects)) {
			do {
				$object = next($this->objects);
			}
			while (
				($object && !is_object($object))
				|| (isset($instance_of) && !class_instanceof($object, $instance_of))
			);
		}
		return $object;
	}

	//------------------------------------------------------------------------------------- getUriRoot
	/**
	 * @return string
	 */
	protected function getUriRoot()
	{
		return Paths::$uri_root;
	}

	//--------------------------------------------------------------------------------- getScriptName
	/**
	 * @return string
	 */
	protected function getScriptName()
	{
		return Paths::$script_name;
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/**
	 * Returns value replacing html entities with coded html, only if this is a displayable value
	 *
	 * @param $value mixed
	 * @return mixed
	 */
	protected function htmlEntities($value)
	{
		return (is_array($value) || is_object($value) || is_resource($value) || !isset($value))
			? $value
			: str_replace(
				array("{",      "}",      "<!--",    "-->"),
				array("&#123;", "&#125;", "&lt;!--", "--&gt;"),
				$value
			);
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse the template replacing templating codes by object's properties and functions results
	 *
	 * @return string html content of the parsed page
	 */
	public function parse()
	{
		$this->parse_class_name = null;
		$content = $this->content;
		$content = $this->parseContainer($content);
		$content = $this->parseFullPage($content);
		return $content;
	}

	//----------------------------------------------------------------------------- parseArrayElement
	/**
	 * @param $array   array
	 * @param $index   string|integer
	 * @return mixed
	 */
	protected function parseArrayElement($array, $index)
	{
		return $this->htmlEntities(isset($array[$index]) ? $array[$index] : null);
	}

	//-------------------------------------------------------------------------------- parseClassName
	/**
	 * @param $class_name string
	 * @return string
	 */
	protected function parseClassName($class_name)
	{
		return Namespaces::fullClassName($class_name);
	}

	//------------------------------------------------------------------------------- parseCollection
	/**
	 * Parse a collection of objects (in case of composition)
	 *
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 * @return string
	 */
	protected function parseCollection(Reflection_Property $property, $collection)
	{
		return (new Html_Builder_Collection($property, $collection))->build();
	}

	//------------------------------------------------------------------------------ parseConditional
	/**
	 * @param $property_name string
	 * @return string|boolean
	 */
	protected function parseConditional($property_name)
	{
		$i = strpos($property_name, "?");
		if ($i !== false) {
			$condition_path = substr($property_name, 0, $i);
			$j = strrpos($property_name, ":");
			if ($this->parseValue($condition_path, true)) {
				if ($j === false) {
					$j = strlen($property_name);
				}
				return $this->parseValue(substr($property_name, $i + 1, $j - $i - 1));
			}
			elseif ($j !== false) {
				return $this->parseValue(substr($property_name, $j + 1));
			}
		}
		return false;
	}

	//------------------------------------------------------------------------------------ parseConst
	/**
	 * Parse a global constant and returns its return value
	 *
	 * @param $object     mixed
	 * @param $const_name string
	 * @return mixed the value of the constant
	 */
	protected function parseConst($object, $const_name)
	{
		return $this->htmlEntities(
			(is_array($object) && isset($object[$const_name])) ? $object[$const_name] : (
				isset($GLOBALS[$const_name]) ? $GLOBALS[$const_name] : (
				isset($GLOBALS["_" . $const_name]) ? $GLOBALS["_" . $const_name] : (
					$this->parseConstSpec($object, $const_name)
				)
			))
		);
	}

	//-------------------------------------------------------------------------------- parseConstSpec
	/**
	 * @param $object     object
	 * @param $const_name string
	 * @return string
	 */
	protected function parseConstSpec(
		/** @noinspection PhpUnusedParameterInspection */
		$object, $const_name
	) {
		switch ($const_name) {
			case "PHPSESSID": return session_id();
		}
		return null;
	}

	//--------------------------------------------------------------------------------- parseConstant
	/**
	 * Parse a string constant delimiter by quotes at start and end
	 *
	 * @param $const_string string
	 * @return string
	 */
	protected function parseConstant($const_string)
	{
		return substr($const_string, 1, -1);
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @param $content string
	 * @return string updated content
	 */
	protected function parseContainer($content)
	{
		if (isset($this->parameters["container"])) {
			$container_begin = "BEGIN:" . $this->parameters["container"];
			$container_end = "END:" . $this->parameters["container"];
		}
		else {
			$container_begin = "BEGIN";
			$container_end = "END";
		}
		$i = strpos($content, "<!--" . $container_begin . "-->");
		if ($i !== false) {
			$i += strlen($container_begin) + 7;
			$j = strrpos($content, "<!--" . $container_end . "-->", $i);
			if (isset($this->parameters["as_widget"]) && $this->parameters["as_widget"]) {
				$content = substr($content, $i, $j - $i);
			}
			else {
				$file_name = $this->main_template;
				$container = $this->getContainerContent($file_name);
				$root_object = (is_object($this->getObject())) ? "<!--@rootObject-->" : "";
				$content = str_replace(
					"{@content}",
					$root_object . substr($content, $i, $j - $i) . $root_object,
					$container
				);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFile
	/**
	 * Parse a property which content is a file object
	 *
	 * @param $property Reflection_Property
	 * @param $file     File
	 * @return string
	 */
	protected function parseFileToString($property, File $file)
	{
		return (new Html_Builder_File($property, $file))->build();
	}

	//--------------------------------------------------------------------------------- parseFullPage
	/**
	 * @param $content string
	 * @return string
	 */
	protected function parseFullPage($content)
	{
		$content = $this->parseVars($content);
		if (!isset($this->parameters["is_included"]) || !$this->parameters["is_included"]) {
			$content = $this->replaceLinks($content);
			$content = $this->replaceUris($content);
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFunc
	/**
	 * Parse a special data / function and returns its return value
	 *
	 * @param $func_name string
	 * @return mixed
	 */
	protected function parseFunc($func_name)
	{
		return $this->htmlEntities($this->callFunc(
			'SAF\Framework\Html_Template_Functions',
			Names::propertyToMethod($func_name, "get")
		));
	}

	//------------------------------------------------------------------------------- parseFuncParams
	/**
	 * Parse a list of function parameters, separated by ","
	 *
	 * Accept quoted "constants" and 'constants'
	 * All other parameters values will be parsed as values
	 *
	 * @param $params_string string
	 * @return mixed
	 */
	protected function parseFuncParams($params_string)
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
				$params[$key] = $this->parseValue($param);
			}
		}
		return $params;
	}

	//---------------------------------------------------------------------------------- parseInclude
	/**
	 * Parses included view controller call result (must be an html view) or includes html template
	 *
	 * @param $include_uri string
	 * @return string included template, parsed
	 */
	protected function parseInclude($include_uri)
	{
		if ((substr($include_uri, -5) === ".html") || (substr($include_uri, -4) === ".php")) {
			// includes html template
			$included = file_get_contents($this->parseIncludeResolve($include_uri));
			if (($i = strpos($included, "<!--BEGIN-->")) !== false) {
				$i += 12;
				$j = strpos($included, "<!--END-->");
				$included = substr($included, $i, $j - $i);
			}
			return $this->parseVars($included);
		}
		else {
			// includes controller result
			return Main_Controller::getInstance()->runController(
				$include_uri, array("is_included" => true)
			);
		}
	}

	//--------------------------------------------------------------------------- parseIncludeResolve
	/**
	 * @param $include_uri string
	 * @return string
	 */
	protected function parseIncludeResolve($include_uri)
	{
		if (($i = strrpos($include_uri, "/")) !== false) {
			$include_uri = substr($include_uri, $i + 1);
		}
		return stream_resolve_include_path($include_uri);
	}

	//------------------------------------------------------------------------------------- parseLoop
	/**
	 * @todo factorize
	 * @param $content string
	 * @param $i       integer
	 * @param $j       integer
	 * @return integer
	 */
	protected function parseLoop(&$content, $i, $j)
	{
		$var_name = substr($content, $i, $j - $i);
		$length = strlen($var_name);
		$i += $length + 3;
		$force_equality = ($var_name[0] === "=");
		$force_condition = (substr($var_name, -1) === "?");
		if (strpos($var_name, ":")) {
			list($var_name, $expr) = explode(":", $var_name);
			if (($sep = strpos($expr, "-")) !== false) {
				$from = substr($expr, 0, $sep);
				$to = substr($expr, $sep + 1);
			}
			else {
				$from = $to = $expr;
			}
			$to = (($to == "") ? null : $to);
			if (!empty($from) && ($from[0] === "{") && (substr($from, -1) === "}")) {
				$from = $this->parseValue(substr($from, 1, -1));
			}
			if (!empty($to) && ($to[0] === "{") && (substr($to, -1) === "}")) {
				$to = $this->parseValue(substr($to, 1, -1));
			}
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
		elseif ($force_equality) {
			$var_name = substr($var_name, 1);
		}
		$loop_content = substr($content, $i, $j - $i);
		$this->removeSample($loop_content);
		$separator = $this->parseSeparator($loop_content);
		$elements = $this->parseValue($var_name, false);
		if (!$force_condition) {
			array_unshift($this->var_names, is_object($elements) ? get_class($elements) : "");
			array_unshift($this->objects, $elements);
		}
		if ($from && !is_numeric($from)) {
			$from = $this->parseValue($from);
		}
		if ($to && !is_numeric($to)) {
			$to = $this->parseValue($to);
		}
		if ($force_equality) {
			$loop_insert = $elements;
		}
		elseif ((is_array($elements) && !$force_condition) || isset($expr)) {
			$first = true;
			$loop_insert = "";
			$counter = 0;
			array_push($this->preprops, $var_name);
			if (is_array($elements)) foreach ($elements as $key => $element) {
				array_push($this->preprops, $element);
				$counter++;
				if (isset($to) && ($counter > $to)) break;
				if ($counter >= $from) {
					array_unshift($this->var_names, $key);
					array_unshift($this->objects, $element);
					if ($first) {
						$first = false;
					}
					else {
						$loop_insert .= $this->parseVars($separator);
					}
					$sub_content = $this->parseVars($loop_content);
					$loop_insert .= $sub_content;
					array_shift($this->objects);
					array_shift($this->var_names);
				}
				array_pop($this->preprops);
			}
			array_pop($this->preprops);
			if (isset($to) && ($counter < $to)) {
				array_unshift($this->var_names, null);
				array_unshift($this->objects, new StdClass());
				while ($counter < $to) {
					$counter++;
					if ($counter >= $from) {
						if ($first) {
							$first = true;
						}
						else {
							$loop_insert .= $this->parseVars($separator);
						}
						$sub_content = $this->parseVars($loop_content);
						$loop_insert .= $sub_content;
					}
				}
				array_shift($this->objects);
				array_shift($this->var_names);
			}
		}
		elseif (is_array($elements)) {
			$loop_insert = empty($elements) ? "" : $this->parseVars($loop_content);
		}
		elseif (is_object($elements)) {
			$loop_insert = $this->parseVars($loop_content);
		}
		elseif (!empty($elements)) {
			$loop_insert = $this->parseVars($loop_content);
		}
		else {
			$loop_insert = "";
		}
		if (!$force_condition) {
			array_shift($this->objects);
			array_shift($this->var_names);
		}
		$i = $i - $length - 7;
		$j = $j + $length2 + 7;
		$content = substr($content, 0, $i) . $loop_insert . substr($content, $j);
		$i += strlen($loop_insert);
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
	 * @param $content string
	 * @return string updated content
	 */
	protected function parseLoops($content)
	{
		$icontent = 0;
		while (($icontent = strpos($content, "<!--", $icontent)) !== false) {
			$i = $icontent + 4;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "-->", $i);
				$this->parseLoop($content, $i, $j);
			}
			else {
				$icontent = strpos($content, "-->", $i) + 3;
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------------- parseMap
	/**
	 * Parse a map of objects (in case of aggregation)
	 *
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 * @return string
	 */
	protected function parseMap(Reflection_Property $property, $collection)
	{
		return (new Html_Builder_Map($property, $collection))->build();
	}

	//----------------------------------------------------------------------------------- parseMethod
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @return string
	 */
	protected function parseMethod($object, $property_name)
	{
		return $this->htmlEntities($object->$property_name());
	}

	//-------------------------------------------------------------------------------------- parseNot
	/**
	 * Returns the reverse boolean value for property value
	 *
	 * @param $property_name string
	 * @return boolean
	 */
	protected function parseNot($property_name)
	{
		return !$this->parseValue(substr($property_name, 1), false);
	}

	//--------------------------------------------------------------------------- parseObjectToString
	/**
	 * @param $object        mixed
	 * @param $property_name string
	 * @return string
	 */
	protected function parseObjectToString(
		/** @noinspection PhpUnusedParameterInspection */
		$object, $property_name
	) {
		return method_exists($object, "__toString") ? $this->htmlEntities($object) : "";
	}

	//-------------------------------------------------------------------------------- parseParameter
	/**
	 * @param $object         mixed
	 * @param $parameter_name string
	 * @return mixed
	 */
	protected function parseParameter(
		/** @noinspection PhpUnusedParameterInspection */
		$object, $parameter_name
	) {
		return $this->htmlEntities(
			isset($this->parameters[$parameter_name]) ? $this->parameters[$parameter_name] : ""
		);
	}

	//----------------------------------------------------------------------------------- parseParent
	/**
	 * @return mixed
	 */
	protected function parseParent()
	{
		array_shift($this->objects);
		array_shift($this->var_names);
		return reset($this->objects);
	}

	//--------------------------------------------------------------------------------- parseProperty
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @return string
	 */
	protected function parseProperty($object, $property_name)
	{
		return $this->htmlEntities(@($object->$property_name));
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Remove <!--separator-->(...) code from a loop content, and returns the separator content
	 *
	 * @param $content string
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

	//------------------------------------------------------------------------------ parseSingleValue
	/**
	 * @param $property_name string
	 * @return mixed
	 */
	protected function parseSingleValue($property_name)
	{
		$source_object = $object = reset($this->objects);
		if (!strlen($property_name)) {
			$object = $this->parseParent();
		}
		elseif (strpos($property_name, "?")) {
			$object = $this->parseConditional($property_name);
		}
		elseif ($property_name[0] == "!") {
			$object = $this->parseNot($property_name);
		}
		elseif (
			($property_name[0] == "'" && substr($property_name, -1) == "'")
			|| ($property_name[0] == '"' && substr($property_name, -1) == '"')
		) {
			$object = $this->parseConstant($property_name);
		}
		elseif (isset($this->parse_class_name)) {
			$object = method_exists($this->parse_class_name, $property_name)
				? $this->parseStaticMethod($this->parse_class_name, $property_name)
				: $this->parseStaticProperty($this->parse_class_name, $property_name);
			$this->parse_class_name = null;
		}
		elseif (($property_name[0] >= 'A') && ($property_name[0] <= 'Z')) {
			if (
				(strlen($property_name) > 1) && ($property_name[1] >= 'a') && ($property_name[1] <= 'z')
			) {
				$this->parse_class_name = $this->parseClassName($property_name);
			}
			else {
				$object = $this->parseConst($object, $property_name);
			}
		}
		elseif ($property_name[0] === "@") {
			$object = $this->parseFunc(substr($property_name, 1));
		}
		elseif ($i = strpos($property_name, "(")) {
			$object = $this->callFunc(reset($this->objects), $property_name);
		}
		elseif (is_array($object)) {
			$object = $this->parseArrayElement($object, $property_name);
		}
		elseif (!is_object($object) && !isset($this->parameters[$property_name])) {
			$object = $this->parseString($object, $property_name);
		}
		elseif (method_exists($object, $property_name)) {
			$object = $this->parseMethod($object, $property_name);
		}
		elseif (isset($object->$property_name)) {
			$object = $this->parseProperty($object, $property_name);
		}
		elseif (isset($this->parameters[$property_name])) {
			$object = $this->parseParameter($object, $property_name);
		}
		else {
			$object = $this->parseProperty($object, $property_name);
		}
		if (($source_object instanceof Reflection_Property) && ($property_name == "value")) {
			$object = (new Reflection_Property_View($source_object))->formatValue($object);
		}
		return $object;
	}

	//----------------------------------------------------------------------------- parseStaticMethod
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @return mixed
	 */
	protected function parseStaticMethod($class_name, $method_name)
	{
		return $this->htmlEntities($class_name::$method_name());
	}

	//--------------------------------------------------------------------------- parseStaticProperty
	/**
	 * @param $class_name    string
	 * @param $property_name string
	 * @return mixed
	 */
	protected function parseStaticProperty($class_name, $property_name)
	{
		return $this->htmlEntities($class_name::$$property_name);
	}

	//----------------------------------------------------------------------------------- parseString
	/**
	 * @param $object        string
	 * @param $property_name string
	 * @return mixed
	 */
	protected function parseString($object, $property_name)
	{
		$string = new String($object);
		return method_exists($string, $property_name)
			? $this->parseStringMethod($string, $property_name)
			: $this->parseStringProperty($string, $property_name);
	}

	//----------------------------------------------------------------------------- parseStringMethod
	/**
	 * @param $object      string
	 * @param $method_name string
	 * @return mixed
	 */
	protected function parseStringMethod($object, $method_name)
	{
		return $this->htmlEntities($object->$method_name());
	}

	//--------------------------------------------------------------------------- parseStringProperty
	/**
	 * @param $object        string
	 * @param $property_name string
	 * @return mixed
	 */
	protected function parseStringProperty($object, $property_name)
	{
		return $this->htmlEntities(isset($object->$property_name) ? $object->$property_name : null);
	}

	//------------------------------------------------------------------------------------- parseThis
	/**
	 * Return true if the text at position $i of $content is a variable, function name, an include
	 *
	 * @param $content string
	 * @param $i       integer
	 * @return boolean
	 */
	protected function parseThis($content, $i)
	{
		$c = $content[$i];
		return (($c >= "a") && ($c <= "z"))
			|| (
				($c >= "A") && ($c <= "Z")
				&& (substr($content, $i, 6) != "BEGIN:") && (substr($content, $i, 4) != "END:")
			)
			|| (strpos("#@/.-+?!|=\"", $c) !== false);
	}

	//------------------------------------------------------------------------------------ parseValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @param $as_string boolean if true, returned value will always be a string
	 * @return string var value after reading value / executing specs (can be an object)
	 */
	protected function parseValue($var_name, $as_string = true)
	{
		if ($var_name === ".") {
			return reset($this->objects);
		}
		elseif ($var_name === "#") {
			return reset($this->var_names);
		}
		elseif ($var_name == "") {
			return "";
		}
		elseif ($var_name[0] === "/") {
			return $this->parseInclude($var_name);
		}
		if (strpos("-+", $var_name[0]) !== false) {
			$descendants_names = $this->descendants_names;
			$descendants = $this->descendants;
			$var_names = $this->var_names;
			$objects = $this->objects;
			while ($var_name[0] === "-") {
				array_unshift($this->descendants_names, array_shift($this->var_names));
				array_unshift($this->descendants, array_shift($this->objects));
				$var_name = substr($var_name, 1);
			}
			while ($var_name[0] === "+") {
				array_unshift($this->var_names, array_shift($this->descendants_names));
				array_unshift($this->objects, array_shift($this->descendants));
				$var_name = substr($var_name, 1);
			}
		}
		$property_name = null;
		/** @var $object mixed */
		if (strpos($var_name, ".") !== false) {
			if (!isset($var_names)) $var_names = $this->var_names;
			if (!isset($objects))   $objects = $this->objects;
			foreach (explode(".", $var_name) as $property_name) {
				$object = $this->parseSingleValue($property_name);
				array_unshift($this->var_names, $property_name);
				array_unshift($this->objects, $object);
			}
		}
		else {
			foreach (explode(".", $var_name) as $property_name) {
				$object = $this->parseSingleValue($property_name);
			}
		}
		if ($as_string && is_object($object)) {
			if ($object instanceof File) {
				$object = $this->parseFileToString(null, $object);
			}
			else {
				$object = $this->parseObjectToString($object, $property_name);
			}
		}
		$this->parse_class_name = null;
		if (isset($objects))           $this->objects = $objects;
		if (isset($var_names)) 	       $this->var_names = $var_names;
		if (isset($descendants))       $this->descendants = $descendants;
		if (isset($descendants_names)) $this->descendants_names = $descendants_names;
		return $object;
	}

	//-------------------------------------------------------------------------------------- parseVar
	/**
	 * @param $content string
	 * @param $i       integer
	 * @param $j       integer
	 * @return mixed
	 */
	protected function parseVar(&$content, $i, $j)
	{
		$var_name = substr($content, $i, $j - $i);
		while (($k = strpos($var_name, "{")) !== false) {
			$this->parseVar($content, $k + $i + 1, $j);
			$j = strpos($content, "}", $i);
			$var_name = substr($content, $i, $j - $i);
		}
		$auto_remove = $this->parseVarWillAutoremove($var_name);
		$value = $this->parseValue($var_name);
		$object = reset($this->objects);
		if (is_array($value) && ($object instanceof Reflection_Property)) {
			$link = $object->getAnnotation("link")->value;
			if ($link === "Collection") {
				$value = $this->parseCollection($object, $value);
			}
			elseif ($link === "Map") {
				$value = $this->parseMap($object, $value);
			}
		}
		$i--;
		if ($auto_remove && !strlen($value)) {
			$this->parseVarRemove($content, $i, $j);
		}
		$content = substr($content, 0, $i) . $value . substr($content, $j + 1);
		$i += strlen($value);
		return $i;
	}

	//-------------------------------------------------------------------------------- parseVarRemove
	/**
	 * @param $content string
	 * @param $i       integer
	 * @param $j       integer
	 */
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
	 * @param $content string
	 * @return string updated content
	 */
	protected function parseVars($content)
	{
		$content = $this->parseLoops($content);
		$i = 0;
		while (($i = strpos($content, "{", $i)) !== false) {
			$i++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, "}", $i);
				$i = $this->parseVar($content, $i, $j);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------ parseVarWillAutoremove
	/**
	 * @param $var_name string
	 * @return bool
	 */
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
	 * @param $content string
	 */
	protected function removeSample(&$content)
	{
		$i = strrpos($content, "<!--sample-->");
		if ($i !== false) {
			if (strpos($content, "<!--", $i + 1) === false) {
				$content = substr($content, 0, $i);
			}
		}
	}

	//----------------------------------------------------------------------------------- replaceLink
	/**
	 * Replace link with correct link path
	 *
	 * @param $link string
	 * @return string
	 */
	protected function replaceLink($link)
	{
		if (strpos($link, "://")) {
			return $link;
		}
		$full_path = str_replace("/./", "/", $this->getUriRoot() . $this->getScriptName() . $link);
		if (substr($full_path, 0, 2) == "./") {
			$full_path = substr($full_path, 2);
		}
		return $full_path;
	}

	//---------------------------------------------------------------------------------- replaceLinks
	/**
	 * Replace links with correct absolute paths into $content
	 *
	 * @param $content string
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
						$replacement_link = $this->replaceLink(substr($content, $i, $j - $i));
						$content = substr($content, 0, $i) . $replacement_link . substr($content, $j);
						$i += strlen($replacement_link);
					}
				}
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------ replaceUri
	/**
	 * Replace URI with correct URI path
	 *
	 * @param $uri string
	 * @return string updated uri
	 */
	protected function replaceUri($uri)
	{
		if (strpos($uri, "://")) {
			return $uri;
		}
		$position = strrpos($uri, "/vendor/");
		$file_name = ($position !== false)
			? substr($uri, $position + 1)
			: substr($uri, strrpos($uri, "/") + 1);
		$file_path = null;
		if (substr($file_name, -4) == ".css") {
			$file_path = static::getCssPath($this->css) . "/" . $file_name;
			if (!file_exists(Paths::$file_root . $file_path)) {
				$file_path = null;
			}
		}
		if (!isset($file_path)) {
			$file_path = substr(
				stream_resolve_include_path($file_name), strlen(Paths::$file_root)
			);
			if (!is_file(Paths::$file_root . $file_path)) {
				$file_path = "unknown";
			}
		}
		return $this->getUriRoot() . $file_path;
	}

	//----------------------------------------------------------------------------------- replaceUris
	/**
	 * Replace URIs with correct URIs paths into $content
	 *
	 * @param $content string
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
				$replaced_uri = $this->replaceUri(substr($content, $i, $j - $i));
				$content = substr($content, 0, $i) . $replaced_uri . substr($content, $j);
				$i += strlen($replaced_uri);
			}
		}
		return $content;
	}

	//---------------------------------------------------------------------------------------- setCss
	/**
	 * @param $css string
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
	 * @param $parameters mixed[] key is parameter name
	 */
	public function setParameters($parameters)
	{
		if (isset($parameters["is_included"]) && $parameters["is_included"]) {
			$parameters["as_widget"] = true;
		}
		$this->parameters = $parameters;
	}

}
