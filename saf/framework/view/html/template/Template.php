<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Application;
use SAF\Framework\Builder;
use SAF\Framework\Controller\Main;
use SAF\Framework\Dao\File;
use SAF\Framework\Reflection\Annotation\Property\Link_Annotation;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Reflection_Property_View;
use SAF\Framework\Tools\Contextual_Callable;
use SAF\Framework\Tools\Names;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Tools\Paths;
use SAF\Framework\Tools\String;
use SAF\Framework\View\Html;
use SAF\Framework\View\Html\Builder\Property;
use SAF\Framework\View\Html\Template\Functions;

/**
 * built-in SAF HTML template engine
 */
class Template
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
	 * Css files relative directory (ie 'default')
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
	protected $descendants = [];

	//---------------------------------------------------------------------------- $descendants_names
	/**
	 * Descendant objects names are set when calls to parents are done, in order to get them back
	 *
	 * @var mixed[]
	 */
	protected $descendants_names = [];

	//-------------------------------------------------------------------------------------- $feature
	/**
	 * Feature name (name of a controller's method, end of the view name)
	 *
	 * @var string
	 */
	protected $feature;

	//--------------------------------------------------------------------------------- $group_values
	/**
	 * Stores the last value for each group var name
	 *
	 * @var string[] key is the group var name, value is the last value
	 */
	protected $group_values;

	//-------------------------------------------------------------------------------- $main_template
	/**
	 * The main template file path (ie 'saf/framework/main.html');
	 *
	 * If null or not set : will be automatically set to current application main template 'main.html'
	 * If false : no main template will be used
	 *
	 * @var string|boolean
	 */
	public $main_template;

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * The objects queue, updated during the parsing
	 *
	 * @var mixed[]
	 */
	public $objects = [];

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
	public $preprops = [];

	//------------------------------------------------------------------------------------------ $use
	/**
	 * Full classes used.
	 *
	 * @example After '<!--use SAF\Framework\Class-->', you can use short class name '{Class}'
	 * @var string[] key is the short class name, value is the full class name including namespace
	 */
	protected $use = [];

	//------------------------------------------------------------------------------------ $var_names
	/**
	 * Var names
	 * Keys correspond to objects keys
	 *
	 * @var string[]
	 */
	public $var_names = [];

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
			array_unshift($this->var_names, 'root');
			array_unshift($this->objects, $object);
		}
		if (isset($template_file)) {
			$this->path    = substr($template_file, 0, strrpos($template_file, SL));
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
	 * @param $func_call   string 'functionName(param1value,param2value,...)' or 'functionName'
	 * @return mixed
	 */
	public function callFunc($object_call, $func_call)
	{
		if ($i = strpos($func_call, '(')) {
			$func_name = substr($func_call, 0, $i);
			$i++;
			$j = strpos($func_call, ')', $i);
			$params = $this->parseFuncParams(substr($func_call, $i, $j - $i));
		}
		else {
			$func_name = $func_call;
			$params = [];
		}
		if (is_a($object_call, Functions::class, true)) {
			if (method_exists($object_call, $func_name)) {
				$params = array_merge([$this], $params);
			}
			else {
				$func_name = substr($func_name, 3);
				$func_name[0] = strtolower($func_name[0]);
				return call_user_func_array($func_name, $params);
			}
		}
		return call_user_func_array([$object_call, $func_name], $params);
	}

	//--------------------------------------------------------------------------- getContainerContent
	/**
	 * @param $file_name string
	 * @return string
	 */
	protected function getContainerContent($file_name)
	{
		$main_template = $this->getMainTemplateFile();
		return $main_template
			? file_get_contents($file_name, !strpos($main_template, SL))
			: '{@content}';
	}

	//------------------------------------------------------------------------------------ getCssPath
	/**
	 * @param $css string
	 * @return string
	 */
	public static function getCssPath($css)
	{
		static $css_path = [];
		$path = isset($css_path[$css]) ? $css_path[$css] : null;
		if (!isset($path)) {
			$path = str_replace(BS, SL, stream_resolve_include_path($css . '/application.css'));
			if ($i = strrpos($path, SL)) {
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
		return isset($this->parameters['feature']) ? $this->parameters['feature'] : $this->feature;
	}

	//--------------------------------------------------------------------------- getMainTemplateFile
	/**
	 * @return string main template file path
	 */
	public function getMainTemplateFile()
	{
		if (!isset($this->main_template)) {
			$directories = Application::current()->include_path->getSourceDirectories();
			while (current($directories) && !isset($this->main_template)) {
				if (file_exists($main_template = current($directories) . '/main.html')) {
					$this->main_template = $main_template;
				}
				next($directories);
			}
		}
		return $this->main_template;
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
			while ($object && (
				!is_object($object) || (isset($instance_of) && !is_a($object, $instance_of))
			));
		}
		return $object;
	}

	//--------------------------------------------------------------------------------- getRootObject
	/**
	 * Gets the root object
	 *
	 * @return object
	 */
	public function getRootObject()
	{
		return end($this->objects);
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

	//----------------------------------------------------------------------------------------- group
	/**
	 * Hide repeated values of a given group
	 *
	 * @param $var_name string
	 * @param $value    string
	 * @return string
	 */
	protected function group($var_name, $value)
	{
		if (
			!isset($this->group_values[$var_name])
			|| ($this->group_values[$var_name] !== strval($value))
		) {
			$this->group_values[$var_name] = strval($value);
			return $value;
		}
		return '';
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
				['{',      '}',      '<!--',    '-->'],
				['&#123;', '&#125;', '&lt;!--', '--&gt;'],
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
		if (!strpos($class_name, BS)) {
			if (isset($this->use[$class_name])) {
				return $this->use[$class_name];
			}
			else {
				return Namespaces::defaultFullClassName($class_name, get_class($this->getRootObject()));
			}
		}
		return $class_name;
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
		return (new Html\Builder\Collection($property, $collection))->build();
	}

	//------------------------------------------------------------------------------ parseConditional
	/**
	 * @param $property_name string
	 * @return string|boolean
	 */
	protected function parseConditional($property_name)
	{
		$i = strpos($property_name, '?');
		if ($i !== false) {
			$condition_path = substr($property_name, 0, $i);
			$j = strrpos($property_name, ':');
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
				isset($GLOBALS['_' . $const_name]) ? $GLOBALS['_' . $const_name] : (
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
			case 'PHPSESSID': return session_id();
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
		if (isset($this->parameters['container'])) {
			$container_begin = 'BEGIN:' . $this->parameters['container'];
			$container_end = 'END:' . $this->parameters['container'];
		}
		else {
			$container_begin = 'BEGIN';
			$container_end = 'END';
		}
		$i = strpos($content, '<!--' . $container_begin . '-->');
		if ($i !== false) {
			$i += strlen($container_begin) + 7;
			$j = strrpos($content, '<!--' . $container_end . '-->', $i);
			if (isset($this->parameters['as_widget'])) {
				$content = substr($content, $i, $j - $i);
			}
			else {
				$file_name = $this->getMainTemplateFile();
				$container = $this->getContainerContent($file_name);
				$root_object = (is_object($this->getObject())) ? '<!--@rootObject-->' : '';
				$content = str_replace(
					'{@content}',
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
		return (new Html\Builder\File($property, $file))->build();
	}

	//--------------------------------------------------------------------------------- parseFullPage
	/**
	 * @param $content string
	 * @return string
	 */
	protected function parseFullPage($content)
	{
		$content = $this->parseVars($content);
		if (!isset($this->parameters['is_included'])) {
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
		$func_name = ($p = strpos($func_name, '('))
			? (Names::propertyToMethod(substr($func_name, 0, $p), 'get') . substr($func_name, $p))
			: Names::propertyToMethod($func_name, 'get');
		return $this->htmlEntities(
			$this->callFunc(Functions::class, $func_name));
	}

	//------------------------------------------------------------------------------- parseFuncParams
	/**
	 * Parse a list of function parameters, separated by ','
	 *
	 * Accept quoted 'constants' and 'constants'
	 * All other parameters values will be parsed as values
	 *
	 * @param $params_string string
	 * @return mixed
	 */
	protected function parseFuncParams($params_string)
	{
		$params = explode(',', $params_string);
		foreach ($params as $key => $param) {
			if (
				((substr($param, 0, 1) == Q) && (substr($param, -1) == Q))
				|| ((substr($param, 0, 1) == DQ) && (substr($param, -1) == DQ))
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
		if ((substr($include_uri, -5) === '.html') || (substr($include_uri, -4) === '.php')) {
			// includes html template
			$included = file_get_contents($this->parseIncludeResolve($include_uri));
			if (($i = strpos($included, '<!--BEGIN-->')) !== false) {
				$i += 12;
				$j = strpos($included, '<!--END-->');
				$included = substr($included, $i, $j - $i);
			}
			return $this->parseVars($included);
		}
		else {
			// includes controller result
			return (new Main())->runController($include_uri, ['is_included' => true]);
		}
	}

	//--------------------------------------------------------------------------- parseIncludeResolve
	/**
	 * @param $include_uri string
	 * @return string
	 */
	protected function parseIncludeResolve($include_uri)
	{
		if (($i = strrpos($include_uri, SL)) !== false) {
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
		$var_name = $search_var_name = substr($content, $i, $j - $i);
		$length = strlen($var_name);
		$i += $length + 3;
		if (substr($var_name, -1) == '>') {
			$end_last = true;
			$var_name = substr($var_name, 0, -1);
		}
		while (($k = strpos($var_name, '{')) !== false) {
			$l = strpos($var_name, '}');
			$this->parseVar($var_name, $k + 1, $l);
		}
		$force_equality = ($var_name[0] === '=');
		$force_condition = (substr($var_name, -1) === '?');
		if (strpos($var_name, ':')) {
			list($var_name, $expr) = explode(':', $var_name);
			$search_var_name = lParse($search_var_name, ':');
			if (($sep = strpos($expr, '-')) !== false) {
				$from = substr($expr, 0, $sep);
				$to = substr($expr, $sep + 1);
			}
			else {
				$from = $to = $expr;
			}
			$to = (($to == '') ? null : $to);
		}
		else {
			$expr = null;
			$from = 0;
			$to = null;
		}
		$length2 = strlen($search_var_name);
		$j = isset($end_last)
			? strrpos($content, '<!--' . $search_var_name . '-->', $j + 3)
			: strpos($content, '<!--' . $search_var_name . '-->', $j + 3);
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
			array_unshift($this->var_names, is_object($elements) ? get_class($elements) : '');
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
			$loop_insert = '';
			$counter = 0;
			$this->preprop($var_name);
			if (is_array($elements)) foreach ($elements as $key => $element) {
				$this->preprop($element);
				$counter++;
				if (isset($to) && ($counter > $to)) break;
				if ($counter >= $from) {
					array_unshift($this->var_names, $key);
					array_unshift($this->objects, $element);
					if ($first) {
						$first = false;
					}
					elseif ($separator) {
						$loop_insert .= $this->parseVars($separator);
					}
					$sub_content = $this->parseVars($loop_content);
					$loop_insert .= $sub_content;
					array_shift($this->objects);
					array_shift($this->var_names);
				}
				$this->preprop();
			}
			$this->preprop();
			if (isset($to) && ($counter < $to)) {
				array_unshift($this->var_names, null);
				array_unshift($this->objects, '');
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
			$loop_insert = empty($elements) ? '' : $this->parseVars($loop_content);
		}
		elseif (is_object($elements)) {
			$loop_insert = $this->parseVars($loop_content);
		}
		elseif (!empty($elements)) {
			$loop_insert = $this->parseVars($loop_content);
		}
		else {
			$loop_insert = '';
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
		$i_content = 0;
		while (($i_content = strpos($content, '<!--', $i_content)) !== false) {
			$i = $i_content + 4;
			if (substr($content, $i, 4) === 'use ') {
				$j = strpos($content, '-->', $i);
				$this->parseUse($content, $i, $j);
			}
			elseif ($this->parseThis($content, $i)) {
				$j = strpos($content, '-->', $i);
				$this->parseLoop($content, $i, $j);
			}
			else {
				$i_content = strpos($content, '-->', $i) + 3;
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
		return (new Html\Builder\Map($property, $collection))->build();
	}

	//----------------------------------------------------------------------------------- parseMethod
	/**
	 * @param $object        object
	 * @param $property_name string
	 * @return string
	 */
	protected function parseMethod($object, $property_name)
	{
		if ($i = strpos($property_name, '(')) {
			$method_name = substr($property_name, 0, $i);
			$i++;
			$j = strpos($property_name, ')', $i);
			$params = $this->parseFuncParams(substr($property_name, $i, $j - $i));
			return $this->htmlEntities(call_user_func_array([$object, $method_name], $params));
		}
		else {
			return $this->htmlEntities($object->$property_name());
		}
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
		return method_exists($object, '__toString') ? $this->htmlEntities($object) : '';
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
			isset($this->parameters[$parameter_name]) ? $this->parameters[$parameter_name] : ''
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
		$class_name = get_class($object);
		if (property_exists($class_name, $property_name)) {
			$getter = (new Reflection_Property($class_name, $property_name))
				->getAnnotation('user_getter')->value;
			if ($getter) {
				$callable = new Contextual_Callable($getter);
				return $callable->call();
			}
		}
		return $this->htmlEntities(@($object->$property_name));
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Removes <!--separator-->(...) code from a loop content, and returns the separator content.
	 *
	 * @param $content string
	 * @return string the separator content
	 */
	protected function parseSeparator(&$content)
	{
		if (($i = strrpos($content, '<!--separator-->')) !== false) {
			$separator = substr($content, $i + 16);
			// this separator is not for me if there is any <!--block--> to parse into it's source code.
			$j = 0;
			while (strpos($separator, '<!--', $j) !== false) {
				$j += 4;
				if ($this->parseThis($separator, $j)) {
					return '';
				}
				$j = strpos($separator, '-->', $j) + 3;
			}
			// nothing to parse inside of it ? This separator is for me.
			$content = substr($content, 0, $i);
			return $separator;
		}
		return '';
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
		elseif ($property_name === '#') {
			return reset($this->var_names);
		}
		elseif (strpos($property_name, '?')) {
			$object = $this->parseConditional($property_name);
		}
		elseif (
			($property_name[0] == Q && substr($property_name, -1) == Q)
			|| ($property_name[0] == DQ && substr($property_name, -1) == DQ)
		) {
			$object = $this->parseConstant($property_name);
		}
		elseif (isset($this->parse_class_name)) {
			$object = method_exists($this->parse_class_name, $property_name)
				? $this->parseStaticMethod($this->parse_class_name, $property_name)
				: (
					property_exists($this->parse_class_name, $property_name)
					? $this->parseStaticProperty($this->parse_class_name, $property_name)
					: isA($this->parse_class_name, $this->parseClassName($property_name))
				);
			$this->parse_class_name = null;
		}
		elseif (($property_name[0] >= 'A') && ($property_name[0] <= 'Z')) {
			if (is_array($object)) {
				$object = $this->parseArrayElement($object, $property_name);
			}
			elseif (
				(strlen($property_name) > 1) && ($property_name[1] >= 'a') && ($property_name[1] <= 'z')
			) {
				$this->parse_class_name = $this->parseClassName($property_name);
			}
			else {
				$object = $this->parseConst($object, $property_name);
			}
		}
		elseif ($property_name[0] === AT) {
			$object = $this->parseFunc(substr($property_name, 1));
		}
		elseif ($i = strpos($property_name, '(')) {
			if (
				(is_object($object) || ctype_upper($object[0]))
				&& method_exists($object, substr($property_name, 0, $i))
			) {
				$object = $this->parseMethod($object, $property_name);
			}
			else {
				$object = $this->callFunc(reset($this->objects), $property_name);
			}
		}
		elseif (is_array($object)) {
			$object = $this->parseArrayElement($object, $property_name);
		}
		elseif (!is_object($object) && !isset($this->parameters[$property_name])) {
			$object = $this->parseString($object, $property_name);
		}
		elseif (
			(is_object($object) || (is_string($object) && !empty($object) && ctype_upper($object[0])))
			&& method_exists($object, $property_name)
		) {
			if (
				($property_name == 'value')
				&& ($object instanceof Reflection_Property)
				&& ($builder = $object->getAnnotation('widget')->value)
				&& is_a($builder, Property::class, true)
			) {
				$builder = Builder::create(
					$builder, [$object, $this->parseMethod($object, $property_name), $this]
				);
				/** @var $builder Property */
				$object = $builder->buildHtml();
			}
			else {
				$object = $this->parseMethod($object, $property_name);
			}
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
		if (($source_object instanceof Reflection_Property) && ($property_name == 'value')) {
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
	 * If property name is the name of a String method, call this method
	 * If not, will return true if string value equals $property_name
	 *
	 * @param $string        string
	 * @param $property_name string
	 * @return mixed
	 */
	protected function parseString($string, $property_name)
	{
		$string = new String($string);
		if (method_exists($string, $property_name)) {
			return $this->parseStringMethod($string, $property_name);
		}
		elseif (property_exists($string, $property_name)) {
			return $this->parseStringProperty($string, $property_name);
		}
		return ($string->value === $property_name);
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
		return ctype_lower($c)
			|| (
				ctype_upper($c)
				&& (substr($content, $i, 6) != 'BEGIN:') && (substr($content, $i, 4) != 'END:')
			)
			|| (strpos('#@/.-+?!|="', $c) !== false);
	}

	//-------------------------------------------------------------------------------------- parseUse
	/**
	 * @param $content string
	 * @param $i       integer
	 * @param $j       integer
	 */
	protected function parseUse(&$content, &$i, $j)
	{
		$class_name = substr($content, $i + 4, $j - $i - 4);
		$this->use[Namespaces::shortClassName($class_name)] = $class_name;
		$content = substr($content, 0, $i - 4) . substr($content, $j + 3);
	}

	//------------------------------------------------------------------------------------ parseValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $var_name  string can be an unique var or path.of.vars
	 * @param $as_string boolean if true, returned value will always be a string
	 * @return string|object var value after reading value / executing specs
	 */
	protected function parseValue($var_name, $as_string = true)
	{
		if ($var_name === DOT) {
			return reset($this->objects);
		}
		elseif ($var_name == '') {
			return '';
		}
		elseif ($var_name[0] === SL) {
			return $this->parseInclude($var_name);
		}
		elseif ($var_name[0] == '!') {
			$not = true;
			$var_name = substr($var_name, 1);
		}
		if (substr($var_name, -1) == '*') {
			$group = true;
			$var_name = substr($var_name, 0, -1);
		}
		if (strpos('-+', $var_name[0]) !== false) {
			$descendants_names = $this->descendants_names;
			$descendants = $this->descendants;
			$var_names = $this->var_names;
			$objects = $this->objects;
			while ($var_name[0] === '-') {
				array_unshift($this->descendants_names, array_shift($this->var_names));
				array_unshift($this->descendants, array_shift($this->objects));
				$var_name = substr($var_name, 1);
			}
			while ($var_name[0] === '+') {
				array_unshift($this->var_names, array_shift($this->descendants_names));
				array_unshift($this->objects, array_shift($this->descendants));
				$var_name = substr($var_name, 1);
			}
		}
		$property_name = null;
		/** @var $object mixed */
		if (strpos($var_name, DOT) !== false) {
			if (!isset($var_names)) $var_names = $this->var_names;
			if (!isset($objects))   $objects   = $this->objects;
			$parenthesis = '';
			foreach (explode(DOT, $var_name) as $property_name) {
				if ($parenthesis) {
					$property_name = $parenthesis . DOT . $property_name;
					$parenthesis = '';
				}
				if (
					strpos($property_name, '(')
					&& (substr_count($property_name, '(') > substr_count($property_name, ')'))
				) {
					$parenthesis = $property_name;
				}
				else {
					$object = $this->parseSingleValue($property_name);
					if (strlen($property_name)) {
						array_unshift($this->var_names, $property_name);
						array_unshift($this->objects,   $object);
					}
				}
			}
		}
		else {
			$object = $this->parseSingleValue($var_name);
		}
		// if the parse value finishes with a class name : check if the last object is any of this class
		if (isset($this->parse_class_name)) {
			$object = isA(reset($this->objects), $this->parse_class_name);
			unset($this->parse_class_name);
		}
		// parse object to string
		if ($as_string && is_object($object)) {
			if ($object instanceof File) {
				$object = $this->parseFileToString(null, $object);
			}
			else {
				$object = $this->parseObjectToString($object, $property_name);
			}
		}
		// parse not
		if (isset($not)) {
			$object = !$object;
		}
		// restore position arrays
		if (isset($objects))           $this->objects = $objects;
		if (isset($var_names)) 	       $this->var_names = $var_names;
		if (isset($descendants))       $this->descendants = $descendants;
		if (isset($descendants_names)) $this->descendants_names = $descendants_names;

		return isset($group) ? $this->group($var_name, $object) : $object;
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
		while (($k = strpos($var_name, '{')) !== false) {
			$this->parseVar($content, $k + $i + 1, $j);
			$j = strpos($content, '}', $i);
			$var_name = substr($content, $i, $j - $i);
		}
		$auto_remove = $this->parseVarWillAutoremove($var_name);
		$value = $this->parseValue($var_name);
		$object = reset($this->objects);
		if (is_array($value) && ($object instanceof Reflection_Property)) {
			$link = $object->getAnnotation('link')->value;
			$value = ($link === Link_Annotation::COLLECTION)
				? $this->parseCollection($object, $value)
				: $this->parseMap($object, $value);
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
			(($content[$i - 1] === Q) && ($content[$j + 1] === Q))
			|| (($content[$i - 1] === DQ) && ($content[$j + 1] === DQ))
			|| (($content[$i - 1] === '|') && ($content[$j + 1] === '|'))
		) {
			$i--;
			$j++;
		}
		while (($content[$i] != SP) && ($content[$i] != ',') && ($content[$i] != SL)) {
			if (($content[$i] == Q) || ($content[$i] == DQ)) {
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
	public function parseVars($content)
	{
		$content = $this->parseLoops($content);
		$i = 0;
		while (($i = strpos($content, '{', $i)) !== false) {
			$i++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, '}', $i);
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
		if ($var_name[0] === '?') {
			$var_name = substr($var_name, 1);
			$auto_remove = true;
		}
		else {
			$auto_remove = false;
		}
		return $auto_remove;
	}

	//--------------------------------------------------------------------------------------- preprop
	/**
	 * @param $preprop object|string
	 */
	protected function preprop($preprop = null)
	{
		if (isset($preprop)) {
			array_push($this->preprops, is_string($preprop)
				? (($i = strrpos($preprop, DOT)) ? substr($preprop, $i + 1) : $preprop)
				: $preprop
			);
		}
		else {
			array_pop($this->preprops);
		}
	}

	//---------------------------------------------------------------------------------- removeSample
	/**
	 * Remove <!--sample-->(...) code from loop content
	 *
	 * @param $content string
	 */
	protected function removeSample(&$content)
	{
		$i = strrpos($content, '<!--sample-->');
		if ($i !== false) {
			if (strpos($content, '<!--', $i + 1) === false) {
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
		if (strpos($link, '://')) {
			return $link;
		}
		$full_path = str_replace(
			[SL . SL, SL . DOT . SL], SL, $this->getUriRoot() . $this->getScriptName() . $link
		);
		if (substr($full_path, 0, 2) == (DOT . SL)) {
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
		$links = ['action=', 'href=', 'location='];
		$quotes = [Q, DQ];
		foreach ($links as $link) {
			foreach ($quotes as $quote) {
				$i = 0;
				while (($i = strpos($content, $link . $quote, $i)) !== false) {
					$i += strlen($link) + 1;
					$j = strpos($content, $quote, $i);
					if (substr($content, $i, 1) === SL) {
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
		if (strpos($uri, '://')) {
			return $uri;
		}
		$position = strrpos($uri, '/vendor/');
		$file_name = ($position !== false)
			? substr($uri, $position + 1)
			: substr($uri, strrpos($uri, SL) + 1);
		$file_path = null;
		if (substr($file_name, -4) == '.css') {
			$file_path = static::getCssPath($this->css) . SL . $file_name;
			if (!file_exists(Paths::$file_root . $file_path)) {
				$file_path = null;
			}
		}
		if (!isset($file_path)) {
			$file_path = substr(
				stream_resolve_include_path($file_name), strlen(Paths::$file_root)
			);
			if (!$file_path || !file_exists(Paths::$file_root . $file_path)) {
				return $this->replaceLink(SL . $uri);
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
		$links = ['@import ' . DQ, 'src=' . DQ];
		foreach ($links as $link) {
			$i = 0;
			while (($i = strpos($content, $link, $i)) !== false) {
				$i += strlen($link);
				$j = strpos($content, DQ, $i);
				$replaced_uri = $this->replaceUri(substr($content, $i, $j - $i));
				$content = substr($content, 0, $i) . $replaced_uri . substr($content, $j);
				$i += strlen($replaced_uri);
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------ setContent
	/**
	 * @param $content string
	 */
	public function setContent($content)
	{
		$this->content = $content;
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
	 * <li>is_included (boolean) : if set, template is included into a page
	 *   main html head and foot will not be loaded
	 * <li>as_widget (boolean) : if set, template is loaded as a widget
	 *   main html head and foot will not be loaded
	 * </ul>
	 *
	 * @param $parameters mixed[] key is parameter name
	 */
	public function setParameters($parameters)
	{
		if (isset($parameters['is_included'])) {
			$parameters['as_widget'] = true;
		}
		$this->parameters = $parameters;
	}

}
