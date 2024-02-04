<?php
namespace ITRocks\Framework\View\Html;

use Error;
use ITRocks\Framework;
use ITRocks\Framework\Application;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Component\Tab;
use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Controller\Parameter;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\File;
use ITRocks\Framework\Http\Uri;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Widget;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Reflection_Property_View;
use ITRocks\Framework\Tools\Contextual_Callable;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Namespaces;
use ITRocks\Framework\Tools\No_Escape;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\Tools\String_Class;
use ITRocks\Framework\Tools\Stringable;
use ITRocks\Framework\View\Html;
use ITRocks\Framework\View\Html\Dom\Anchor;
use ITRocks\Framework\View\Html\Dom\Div;
use ITRocks\Framework\View\Html\Template\Functions;
use ITRocks\Framework\View\Html\Template\Loop;

/**
 * Built-in ITRocks HTML template engine
 */
class Template
{

	//----------------------------------------------------------------------------- options constants
	const ABSOLUTE_LINKS     = 'absolute_links';
	const HIDE_PAGE_FRAME    = 'hide_page_frame';
	const ORIGIN             = '@-OrIgIn-@';
	const PROPAGATE          = true;
	const TEMPLATE           = 'template';
	const TEMPLATE_CLASS     = 'template_class';
	const TEMPLATE_FUNCTIONS = 'template_functions';
	const TEMPLATE_NAMESPACE = 'template_namespace';

	//-------------------------------------------------------------------------------------- $content
	/** Content of the template file, changed by calculated result HTML content during parse() */
	protected string $content;

	//------------------------------------------------------------------------------------- $counters
	/**
	 * Contextual counters
	 *
	 * @see Functions::getCounter
	 * @var array integer[string $context_class_name][string $context_identifier][string $class_name]
	 */
	public array $counters;

	//------------------------------------------------------------------------------------------ $css
	/** Css files relative directory (ie 'default') */
	protected string $css;

	//---------------------------------------------------------------------------------- $descendants
	/** Descendant objects are set when calls to parents are done, in order to get them back */
	protected array $descendants = [];

	//---------------------------------------------------------------------------- $descendants_names
	/**
	 * Descendant objects names are set when calls to parents are done, in order to get them back
	 *
	 * @var string[]
	 */
	protected array $descendants_names = [];

	//-------------------------------------------------------------------------------------- $feature
	/** Feature name (name of a controller's method, end of the view name) */
	protected string $feature = '';

	//------------------------------------------------------------------------------------ $functions
	/** @var Functions */
	protected Functions $functions;

	//--------------------------------------------------------------------------------- $group_values
	/**
	 * Stores the last value for each group var name
	 *
	 * @var string[] key is the group var name, value is the last value
	 */
	protected array $group_values;

	//------------------------------------------------------------------------------------- $included
	/**
	 * Keys are :
	 * - $include_path : the /include/file/path.html
	 * - $class_name : the contextual class name : can be '' in case of 'no-context'
	 *
	 * @var array string $prepared_content[string $include_path][string $class_name]
	 */
	protected array $included = [];

	//--------------------------------------------------------------------------------- $link_objects
	public bool $link_objects = true;

	//-------------------------------------------------------------------------------- $main_template
	/**
	 * The main template file path (ie 'itrocks/framework/main.html');
	 *
	 * If null or not set : will be automatically set to current application main template 'main.html'
	 * If false : no main template will be used
	 */
	public string|null $main_template = null;

	//-------------------------------------------------------------------------------------- $objects
	/** The objects queue, updated during the parsing */
	public array $objects = [];

	//----------------------------------------------------------------------------------- $parameters
	protected array $parameters;

	//----------------------------------------------------------------------------- $parse_class_name
	/** Currently parsing full class name, for static calls like {User.current} */
	public string $parse_class_name = '';

	//----------------------------------------------------------------------------------------- $path
	/** Template file path (base for css / javascript links) */
	protected string $path = '';

	//---------------------------------------------------------------------------- $properties_prefix
	/**
	 * This prepares properties prefix for @edit calls : each loop adds the property name and value to
	 * $properties_prefix
	 *
	 * @var string[]
	 */
	public array $properties_prefix = [];

	//------------------------------------------------------------------------------------------ $use
	/**
	 * Full classes used.
	 *
	 * @example After '<!--use ITRocks\Framework\Class-->', you can use short class name '{Class}'
	 * @var string[] key is the short class name, value is the full class name including namespace
	 */
	protected array $use = [];

	//------------------------------------------------------------------------------------ $var_names
	/** @var string[] Var names : keys matches object keys */
	public array $var_names = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructs a template object, initializing the source data object and the template access path
	 *
	 * @param $object        mixed|null
	 * @param $template_file string|null full path to template file
	 * @param $feature_name  string|null feature name
	 */
	public function __construct(
		mixed $object = null, string $template_file = null, string $feature_name = null
	) {
		if (isset($feature_name)) {
			$this->feature = $feature_name;
			if (!isset($template_file)) {
				$template_file = Engine::getTemplateFile(get_class($object), [$feature_name]);
			}
		}
		if (isset($object)) {
			$this->unshift('root', $object);
		}
		if (isset($template_file)) {
			$this->path = substr($template_file, 0, strrpos($template_file, SL));
			if (!file_exists($template_file)) {
				trigger_error('Template file not found ' . $template_file, E_USER_ERROR);
			}
			$this->content = file_get_contents($template_file);
		}
	}

	//---------------------------------------------------------------------------- articleClassToBody
	protected function articleClassToBody(string &$content) : void
	{
		if (
			!($position = strpos($content, '<main'))
			|| !($position     = strpos($content, '<article', $position))
			|| !($end_position = strpos($content, '>', $position))
			|| !($position     = strpos($content, ' class=', $position))
			|| ($position > $end_position)
			|| !($end_position = strpos($content, DQ, $position += 8))
		) {
			return;
		}
		$classes = substr($content, $position, $end_position - $position);
		if (
			!($position = strpos($content, '<body'))
			|| !($end_position = strpos($content, '>', $position += 5))
		) {
			return;
		}
		if (
			($data_class_position = strpos($content, 'data-class=', $position))
			&& ($data_class_position < $end_position)
			&& ($data_class_end_position = strpos($content, DQ, $data_class_position += 12))
		) {
			$data_class = substr(
				$content, $data_class_position, $data_class_end_position - $data_class_position
			);
		}
		else {
			$data_class = null;
		}
		$add_classes = (' class=' . DQ . $classes . DQ)
			. ($data_class ? (DQ . ' data-class=' . DQ . $data_class . DQ) : '');
		$content = substr($content, 0, $position) . $add_classes . substr($content, $position);
	}

	//--------------------------------------------------------------------------------- backupContext
	/**
	 * @return array [string[], array, string[]] [$var_names, $objects, $translation_contexts]
	 * @see parseValue(), restoreContext()
	 */
	protected function backupContext() : array
	{
		return [$this->var_names, $this->objects, Loc::$contexts_stack];
	}

	//----------------------------------------------------------------------------- backupDescendants
	/**
	 * @return array [string[], array] [$descendants_names, $descendants]
	 * @see parseValue(), restoreDescendants()
	 */
	protected function backupDescendants() : array
	{
		return [$this->descendants_names, $this->descendants];
	}

	//--------------------------------------------------------------------------------- blackZonesInc
	/**
	 * Increment black zones offset starting from a $position by $increment
	 *
	 * @param $black_zones integer[]
	 * @param $increment   integer
	 * @param $position    integer
	 */
	protected function blackZonesInc(array &$black_zones, int $increment, int $position = 0) : void
	{
		$new_black_zones = [];
		foreach ($black_zones as $start => $stop) {
			if ($start >= $position) {
				$start += $increment;
				$stop  += $increment;
			}
			$new_black_zones[$start] = $stop;
		}
		$black_zones = $new_black_zones;
	}

	//---------------------------------------------------------------------------------- blackZonesOf
	/**
	 * Search "black zones" into content
	 *
	 * @param $content    string   the text to search black zones into
	 * @param $delimiters string[] each key is the start delimiter, value is the end delimiter
	 * @return integer[] key is the start index and value is the end index for each black zone
	 */
	protected function blackZonesOf(string $content, array $delimiters) : array
	{
		$black_zones = [];
		foreach ($delimiters as $start => $stop) {
			$i = 0;
			while (($i = strpos($content, $start, $i)) !== false) {
				$j = strpos($content, $stop, $i + strlen($start));
				if ($j === false) {
					$j = strlen($content);
				}
				else {
					$j += strlen($stop);
				}
				$black_zones[$i] = $j;
				$i = $j;
			}
		}
		ksort($black_zones);
		return $black_zones;
	}

	//-------------------------------------------------------------------------------------- callFunc
	/**
	 * Calls a function and returns result
	 *
	 * @param $object_call object|string object or class name
	 * @param $func_call   string 'functionName(param1value,param2value,...)' or 'functionName'
	 * @return mixed
	 */
	public function callFunc(object|string $object_call, string $func_call) : mixed
	{
		if ($i = strpos($func_call, '(')) {
			$func_name = substr($func_call, 0, $i);
			$i ++;
			$j      = strpos($func_call, ')', $i);
			$params = $this->parseFuncParams(substr($func_call, $i, $j - $i));
		}
		else {
			$func_name = $func_call;
			$params    = [];
		}
		if (is_a($object_call, Functions::class, true)) {
			if (method_exists($object_call, $func_name)) {
				$params = array_merge([$this], $params);
			}
			else {
				$func_name    = substr($func_name, 3);
				$func_name[0] = strtolower($func_name[0]);
				return call_user_func_array($func_name, $params);
			}
		}
		return call_user_func_array([$object_call, $func_name], $params);
	}

	//--------------------------------------------------------------------------------------- context
	/** Returns the context for this template, may be overridden to change context generation */
	public function context() : string
	{
		foreach ($this->objects as $object) {
			if (is_object($object)) {
				return Builder::current()->sourceClassName(get_class($object));
			}
			if (is_string($object) && class_exists($object, false)) {
				return Builder::current()->sourceClassName($object);
			}
		}
		return get_class($this);
	}

	//--------------------------------------------------------------------------- getContainerContent
	protected function getContainerContent(string $file_name) : string
	{
		$main_template = $this->getMainTemplateFile();
		return $main_template
			? file_get_contents($file_name, !str_contains($main_template, SL))
			: '{@content}';
	}

	//------------------------------------------------------------------------------------ getCssPath
	public static function getCssPath(string $css) : string
	{
		static $css_path = [];
		$path = $css_path[$css] ?? null;
		if (!isset($path)) {
			$path = str_replace(BS, SL, stream_resolve_include_path($css . '/html.css'));
			if ($i = strrpos($path, SL)) {
				$path = substr($path, 0, $i);
			}
			$path           = substr($path, strlen(realpath(Paths::$file_root)) + 1);
			$css_path[$css] = $path;
		}
		return $path;
	}

	//------------------------------------------------------------------------------------ getFeature
	public function getFeature() : string
	{
		return $this->parameters['feature'] ?? $this->feature;
	}

	//---------------------------------------------------------------------------------- getHeadLinks
	/** @return string[] */
	public function getHeadLinks(string $content) : array
	{
		$links = [];
		$j     = 0;
		while (($i = strpos($content, '<link rel=', $j)) !== false) {
			$j       = strpos($content, '>', $i) + 1;
			$links[] = substr($content, $i, $j - $i);
		}
		return $links;
	}

	//---------------------------------------------------------------------------------- getHeadMetas
	/** @return string[] */
	protected function getHeadMetas(string $content) : array
	{
		$metas = [];
		$j     = 0;
		while (($i = strpos($content, '<meta', $j)) !== false) {
			$j       = strpos($content, '>', $i) + 1;
			$metas[] = substr($content, $i, $j - $i);
		}
		return $metas;
	}

	//---------------------------------------------------------------------------------- getHeadTitle
	public function getHeadTitle(string $content) : string
	{
		if (($i = strpos($content, '<title')) === false) {
			return '';
		}
		$j = strpos($content, '</title>', $i) + 8;
		return substr($content, $i, $j - $i);
	}

	//--------------------------------------------------------------------------- getMainTemplateFile
	/** @return string main template file path */
	public function getMainTemplateFile() : string
	{
		if (isset($this->main_template)) {
			return $this->main_template;
		}
		$directories = Application::current()->include_path->getSourceDirectories();
		while (current($directories) && !isset($this->main_template)) {
			if (file_exists($main_template = current($directories) . '/main.html')) {
				$this->main_template = $main_template;
			}
			next($directories);
		}
		return $this->main_template;
	}

	//------------------------------------------------------------------------------------- getObject
	/** Gets the template top object */
	public function getObject() : mixed
	{
		return reset($this->objects);
	}

	//---------------------------------------------------------------------------------- getParameter
	/**
	 * Gets parameter value
	 *
	 * @param $parameter class-string<T>
	 * @return array|T|string|null
	 * @template T
	 */
	public function getParameter(string $parameter) : array|object|string|null
	{
		return $this->parameters[$parameter] ?? null;
	}

	//------------------------------------------------------------------------------- getParentObject
	/**
	 * Gets the first parent of current (last) object that is an object
	 *
	 * @param $instance_of class-string<T>|null class name
	 * @return ?T
	 * @template T
	 */
	public function getParentObject(string $instance_of = null) : ?object
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
		return $object ?: null;
	}

	//--------------------------------------------------------------------------------- getRootObject
	/** Gets the root object */
	public function getRootObject() : object
	{
		return end($this->objects);
	}

	//--------------------------------------------------------------------------------- getScriptName
	protected function getScriptName() : string
	{
		return Paths::$script_name ?: substr(Paths::$uri_base, 1);
	}

	//------------------------------------------------------------------------------------ getUriRoot
	protected function getUriRoot() : string
	{
		return Paths::$uri_root;
	}

	//----------------------------------------------------------------------------------------- group
	/** Hide repeated values of a given group */
	protected function group(string $var_name, string $value) : string
	{
		if (!isset($this->group_values[$var_name]) || ($this->group_values[$var_name] !== $value)) {
			$this->group_values[$var_name] = $value;
			return $value;
		}
		return '';
	}

	//---------------------------------------------------------------------------------- htmlEntities
	/** Returns value replacing html entities with coded html, only if this is a displayable value */
	protected function htmlEntities(string $value) : string
	{
		return str_ireplace(
			['|',      '{'     , '}'     , '<!--'   , '-->'   , '<script',    '</script'],
			['&#124;', '&#123;', '&#125;', '&lt;!--', '--&gt;', '&lt;script', '&lt;/script'],
			$value
		);
	}

	//-------------------------------------------------------------------------------- isInBlackZones
	/**
	 * Returns true if the $position is inside any of the $black_zones
	 *
	 * @param $black_zones integer[]
	 * @param $position    integer
	 * @return boolean
	 */
	protected function isInBlackZones(array $black_zones, int $position) : bool
	{
		foreach ($black_zones as $start => $stop) {
			if (($start <= $position) && ($position <= $stop)) {
				return true;
			}
		}
		return false;
	}

	//---------------------------------------------------------------------------------- newFunctions
	protected function newFunctions() : Functions
	{
		/** @noinspection PhpUnhandledExceptionInspection template functions class must be valid */
		/** @var $functions Functions */
		$functions = Builder::create($this->parameters[self::TEMPLATE_FUNCTIONS] ?? Functions::class);
		return $functions;
	}

	//----------------------------------------------------------------------------- originValueAddDiv
	protected function originValueAddDiv(mixed $value, Reflection_Property $property) : mixed
	{
		$div = new Div($value);
		if (
			method_exists($property, 'tooltip')
			&& property_exists($property, 'tooltip')
			&& $property->tooltip
		) {
			$div->setData('tooltip', $property->tooltip);
		}
		return strval($div);
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * Parse the template replacing templating codes by object's properties and functions results
	 *
	 * @return string html content of the parsed page
	 */
	public function parse() : string
	{
		$this->parse_class_name = '';
		$content = $this->content;
		$content = $this->parseContainer($content);
		return $this->parseFullPage($content);
	}

	//----------------------------------------------------------------------------- parseArrayElement
	protected function parseArrayElement(array $array, int|string $index) : mixed
	{
		return $array[$index] ?? null;
	}

	//-------------------------------------------------------------------------------- parseClassName
	protected function parseClassName(string $class_name) : string
	{
		if (!str_contains($class_name, BS)) {
			$class_name = $this->use[$class_name]
				?? Namespaces::defaultFullClassName($class_name, get_class($this->getRootObject()));
		}
		return Builder::className($class_name);
	}

	//------------------------------------------------------------------------------- parseCollection
	/**
	 * Parse a collection of objects (in case of composition)
	 *
	 * @param $property   Reflection_Property
	 * @param $collection object[]
	 * @return string
	 */
	protected function parseCollection(Reflection_Property $property, array $collection) : string
	{
		$type = $property->getType();
		return $type->isAbstractClass()
			? (new Html\Builder\Abstract_Collection($property, $collection))->build()
			: (new Html\Builder\Collection($property, $collection))->build();
	}

	//------------------------------------------------------------------------------ parseConditional
	protected function parseConditional(string $property_name) : bool|string
	{
		$i = strpos($property_name, '?');
		if ($i === false) {
			return false;
		}
		$condition_path = substr($property_name, 0, $i);
		$condition      = $this->parseValue($condition_path);
		$j              = strrpos($property_name, ':');
		if ($condition) {
			if ($j === false) {
				$j = strlen($property_name);
			}
			elseif ($j === ($i + 1)) {
				return $condition;
			}
			return $this->parseValue(substr($property_name, $i + 1, $j - $i - 1));
		}
		elseif ($j !== false) {
			return $this->parseValue(substr($property_name, $j + 1));
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
	protected function parseConst(mixed $object, string $const_name) : mixed
	{
		if (is_array($object) && isset($object[$const_name])) {
			$value = $object[$const_name];
		}
		elseif (isset($GLOBALS[$const_name])) {
			$value = $GLOBALS[$const_name];
		}
		elseif (isset($GLOBALS['_' . $const_name])) {
			$value = $GLOBALS['_' . $const_name];
		}
		elseif (defined($const_name)) {
			$value = constant($const_name);
		}
		else {
			$value = $this->parseConstSpec($object, $const_name);
		}
		return $value;
	}

	//-------------------------------------------------------------------------------- parseConstSpec
	protected function parseConstSpec(
		/** @noinspection PhpUnusedParameterInspection */ mixed $object, string $const_name
	) : ?string
	{
		return ($const_name === 'PHPSESSID') ? session_id() : null;
	}

	//--------------------------------------------------------------------------------- parseConstant
	/** Parse a string constant delimiter by quotes at start and end */
	protected function parseConstant(string $const_string) : string
	{
		return substr($const_string, 1, -1);
	}

	//-------------------------------------------------------------------------------- parseContainer
	/**
	 * Replace code before <!--BEGIN--> and after <!--END--> by the html main container's code
	 *
	 * @return string updated content
	 */
	protected function parseContainer(string $content) : string
	{
		if (isset($this->parameters[Parameter::CONTAINER])) {
			$container_begin = 'BEGIN:' . $this->parameters[Parameter::CONTAINER];
			$container_end   = 'END:' . $this->parameters[Parameter::CONTAINER];
		}
		else {
			$container_begin = 'BEGIN';
			$container_end   = 'END';
		}
		$i = strpos($content, '<!--' . $container_begin . '-->');
		if ($i !== false) {
			$i += strlen($container_begin) + 7;
			$j = strrpos($content, '<!--' . $container_end . '-->', $i);
			if (isset($this->parameters[Parameter::AS_WIDGET])) {
				$content = substr($content, $i, $j - $i);
			}
			else {
				$file_name = $this->getMainTemplateFile();
				$container = $this->getContainerContent($file_name);
				$links     = $this->getHeadLinks($content);
				$metas     = $this->getHeadMetas($content);
				$title     = $this->getHeadTitle($content);

				$root_begin = (is_object($this->getObject())) ? '<!--@rootObject-->' : '';
				$root_end   = (!$root_begin || !str_contains($container . $content, '<!--end-->'))
					? $root_begin
					: '<!--end-->';

				$content = str_replace(
					'{@content}',
					$root_begin . substr($content, $i, $j - $i) . $root_end,
					$container
				);

				$this->articleClassToBody($content);
				$this->replaceHeadLinks($content, $links);
				$this->replaceHeadMetas($content, $metas);
				$this->replaceHeadTitle($content, $title);
			}
		}
		return $content;
	}

	//---------------------------------------------------------------------------------- parseContent
	public function parseContent(string $content) : string
	{
		$content = $this->removeComments($content);
		$content = $this->removeAppAttributes($content);
		$content = $this->prepareW3Links($content);
		$content = $this->parseVars($content);
		return $this->removeAppLinks($content);
	}

	//----------------------------------------------------------------------------- parseFileToString
	/** Parse a property which content is a file object */
	protected function parseFileToString(File $file, Reflection_Property $property = null) : string
	{
		return (new Html\Builder\File($file, $property))->build();
	}

	//--------------------------------------------------------------------------------- parseFullPage
	protected function parseFullPage(string $content) : string
	{
		$content = $this->parseContent($content);
		if (!isset($this->parameters[Parameter::IS_INCLUDED])) {
			$content = $this->replaceLinks($content);
			$content = $this->replaceUris($content);
		}
		return $content;
	}

	//------------------------------------------------------------------------------------- parseFunc
	/** Parse a special data / function and returns its return value */
	protected function parseFunc(string $func_name) : mixed
	{
		$func_name = ($p = strpos($func_name, '('))
			? (Names::propertyToMethod(substr($func_name, 0, $p), 'get') . substr($func_name, $p))
			: Names::propertyToMethod($func_name, 'get');
		return $this->callFunc($this->functions, $func_name);
	}

	//------------------------------------------------------------------------------- parseFuncParams
	/**
	 * Parse a list of function parameters, separated by ','
	 *
	 * Accept quoted 'constants' and 'constants'
	 * All other parameters values will be parsed as values
	 */
	protected function parseFuncParams(string $params_string) : array
	{
		$params = explode(',', $params_string);
		foreach ($params as $key => $param) {
			$param = trim($param);
			if (
				(str_starts_with($param, Q) && str_ends_with($param, Q))
				|| (str_starts_with($param, DQ) && str_ends_with($param, DQ))
			) {
				$params[$key] = substr($param, 1, -1);
			}
			else {
				$params[$key] = is_numeric($param) ? $param : $this->parseValue($param);
			}
		}
		return $params;
	}

	//---------------------------------------------------------------------------------- parseInclude
	/**
	 * Parses included view controller call result (must be a html view) or includes html template
	 *
	 * @param $include_uri string
	 * @return string included template, parsed, or null if included file was not found
	 */
	protected function parseInclude(string $include_uri) : string
	{
		return strEndsWith($include_uri, ['.html', '.php'])
			? $this->parseIncludeTemplate($include_uri)
			: $this->parseIncludeController($include_uri);
	}

	//------------------------------------------------------------------------- parseIncludeClassName
	protected function parseIncludeClassName(string $include_uri) : string
	{
		if (ctype_lower($include_uri[0]) && str_contains($include_uri, SL)) {
			return '';
		}
		return str_contains($include_uri, SL)
			? Names::pathToClass(lLastParse($include_uri, SL))
			: get_class(reset($this->objects));
	}

	//------------------------------------------------------------------------ parseIncludeController
	protected function parseIncludeController(string $include_uri) : string
	{
		$options = [Parameter::IS_INCLUDED => true];
		if (static::PROPAGATE && (get_class($this) !== __CLASS__)) {
			$options[self::TEMPLATE_CLASS] = get_class($this);
		}
		// relative controller : on current object
		if (str_starts_with($include_uri, SL) && ctype_lower(substr($include_uri, 1, 1))) {
			$include_uri = Framework\View::link($this->functions->getObject($this)) . $include_uri;
		}
		return (new Main)->runController($include_uri, $options);
	}

	//--------------------------------------------------------------------------- parseIncludeResolve
	protected function parseIncludeResolve(string $include_uri, string $class_name) : string
	{
		if (isset($GLOBALS['D'])) {
			echo '- resolve ' . $include_uri . ' (context:' . $class_name . ')' . BR;
		}
		if ($class_name) {
			$feature_name = lParse(rLastParse($include_uri, SL, 1, true), '.html');
			$resolve      = Engine::getTemplateFile($class_name, [$feature_name]);
		}
		else {
			$resolve = stream_resolve_include_path($include_uri);
		}
		if (isset($GLOBALS['D'])) {
			echo '- FOUND INCLUDE ' . Paths::getRelativeFileName($resolve) . BR;
		}
		return $resolve;
	}

	//-------------------------------------------------------------------------- parseIncludeTemplate
	protected function parseIncludeTemplate(string $include_uri) : string
	{
		if (isset($GLOBALS['D'])) echo '- include ' . $include_uri . BR;
		// includes html template
		if (str_starts_with($include_uri, SL)) {
			$include_uri = substr($include_uri, 1);
		}
		$class_name = $this->parseIncludeClassName($include_uri);
		if (!isset($this->included[$include_uri][$class_name])) {
			$file_name = $this->parseIncludeResolve($include_uri, $class_name);
			if ($file_name) {
				$included = file_get_contents($file_name);
				if (($i = strpos($included, '<!--BEGIN-->')) !== false) {
					$i += 12;
					$j = strrpos($included, '<!--END-->');
					$this->included[$include_uri][$class_name] = substr($included, $i, $j - $i);
				}
				else {
					$this->included[$include_uri][$class_name] = $included;
				}
			}
			else {
				trigger_error('Could not resolve' . SP . $include_uri, E_USER_ERROR);
			}
		}
		return isset($this->included[$include_uri][$class_name])
			? $this->parseContent($this->included[$include_uri][$class_name])
			: '';
	}

	//------------------------------------------------------------------------------------- parseLoop
	protected function parseLoop(string &$content, int $i, int $j) : int
	{
		$end_j          = $j;
		$loop           = new Loop();
		$loop->use_end  = strpos($content, '<!--end-->', $j);
		$loop->var_name = substr($content, $i, $j - $i);
		$length         = strlen($loop->var_name);
		if (
			str_starts_with($loop->var_name, 'foreach ')
			|| str_starts_with($loop->var_name, 'with ')
		) {
			$loop->var_name = substr($loop->var_name, strpos($loop->var_name, SP) + 1);
		}
		if (str_starts_with($loop->var_name, 'if ')) {
			$loop->var_name = substr($loop->var_name, strpos($loop->var_name, SP) + 1);
			if (!str_ends_with($loop->var_name, '?')) {
				$loop->var_name .= '?';
			}
		}
		$length_end         = $this->parseLoopVarName($loop, $content, $else_j, $end_j);
		$i                 += $length + 3;
		$is_target          = false;
		$loop->content      = substr($content, $i, $else_j - $i);
		$loop->else_content = ($else_j === $end_j)
			? ''
			: substr($content, $else_j + 11, $end_j - $else_j - 11);
		$this->parseLoopContentSections($loop);
		if (str_starts_with($loop->var_name, 'target ')) {
			$elements = null;
			if (isset($this->parameters[Parameter::AS_WIDGET])) {
				$is_target = true;
				$elements  = reset($this->objects);
			}
			$loop->force_condition = true;
		}
		else {
			$elements = $this->parseValue($loop->var_name);
		}
		if (($elements || !is_array($elements)) && !$loop->force_condition) {
			$this->unshift(is_object($elements) ? get_class($elements) : '', $elements);
		}
		if ($loop->from && !is_numeric($loop->from)) {
			$loop->from = $this->parseValue($loop->from, true);
		}
		if ($loop->to && !is_numeric($loop->to)) {
			$loop->to = $this->parseValue($loop->to, true);
		}
		if ($loop->force_equality) {
			$loop_insert = $elements;
		}
		elseif ((is_array($elements) && !$loop->force_condition) || isset($loop->has_expr)) {
			$loop_insert = $this->parseLoopArray($loop, $elements);
		}
		elseif (is_array($elements)) {
			$loop_insert = ($elements || ($else_j === $end_j))
				? ($elements ? $this->parseVars($loop->content) : '')
				: $this->parseVars($loop->else_content);
		}
		elseif (is_object($elements)) {
			$loop_insert = $this->parseVars($loop->content);
			if (!strlen(trim($loop_insert)) && ($else_j !== $end_j)) {
				$loop_insert = $this->parseVars($loop->else_content);
			}
		}
		elseif (!empty($elements)) {
			$loop_insert = $this->parseVars($loop->content);
		}
		else {
			$loop_insert = ($else_j === $end_j) ? '' : $this->parseVars($loop->else_content);
		}
		if (($elements || !is_array($elements)) && !$loop->force_condition) {
			$this->shift();
		}
		if ($is_target) {
			$loop_insert = '<!--' . $loop->var_name . '-->' . $loop_insert . '<!--end-->';
		}
		$i       = $i - $length - 7;
		$j       = $end_j + $length_end + 7;
		$content = substr($content, 0, $i) . $loop_insert . substr($content, $j);
		$i      += strlen($loop_insert);
		return $i;
	}

	//-------------------------------------------------------------------------------- parseLoopArray
	protected function parseLoopArray(Loop $loop, array $elements) : string
	{
		$loop_insert = '';
		$property    = reset($this->objects);
		if ($property instanceof Reflection_Property) {
			$this->properties_prefix[] = $property->path;
		}
		foreach ($elements as $loop->key => $loop->element) {
			$parsed_element = $this->parseLoopElement($loop);
			if (is_null($parsed_element)) break;
			$loop_insert .= $parsed_element;
		}
		if (!$elements && strlen($loop->else_content)) {
			$loop_insert = $this->parseLoopElement($loop, true);
		}
		if ($property instanceof Reflection_Property) {
			array_pop($this->properties_prefix);
		}
		if (isset($loop->to) && ($loop->counter < $loop->to)) {
			$loop_insert .= $this->parseLoopEmptyElements($loop);
		}
		return $loop_insert;
	}

	//---------------------------------------------------------------------- parseLoopContentSections
	protected function parseLoopContentSections(Loop $loop) : void
	{
		$this->removeSample($loop);
		$this->parseLoopId($loop);
		$this->parseLoopId($loop);
		$loop->separator = $this->parseSeparator($loop);
	}

	//------------------------------------------------------------------------------ parseLoopElement
	protected function parseLoopElement(Loop $loop, bool $else = false) : ?string
	{
		if (is_numeric($loop->key)) {
			if (!$this->properties_prefix) {
				reset($this->objects);
				$object = next($this->objects);
				if (
					is_object($object)
					&& property_exists($object, $loop->var_name)
					&& !($object instanceof Tab)
				) {
					$this->properties_prefix[] = $loop->var_name;
					$pop_properties_prefix = true;
				}
			}
			if ($this->properties_prefix || !is_numeric($loop->key)) {
				$this->properties_prefix[] = $loop->key;
			}
		}
		$loop->counter ++;
		$loop_insert   = '';
		if (isset($loop->to) && ($loop->counter > $loop->to)) {
			$loop_insert = null;
		}
		elseif ($loop->counter >= $loop->from) {
			if (!$else) {
				$this->unshift($loop->key, $loop->element);
			}
			if ($loop->first) {
				$loop->first = false;
			}
			elseif ($loop->separator) {
				$loop_insert = $this->parseVars($loop->separator);
			}
			$loop_insert .= $this->parseVars($else ? $loop->else_content : $loop->content);
			if (!$else) {
				$this->shift();
			}
		}
		if (is_numeric($loop->key)) {
			array_pop($this->properties_prefix);
			if ($pop_properties_prefix ?? false) {
				array_pop($this->properties_prefix);
			}
		}
		if ($loop_insert && str_starts_with($loop_insert, LF) && str_ends_with($loop_insert, LF)) {
			$loop_insert = substr($loop_insert, 1);
		}
		return $loop_insert;
	}

	//------------------------------------------------------------------------ parseLoopEmptyElements
	protected function parseLoopEmptyElements(Loop $loop) : string
	{
		$loop_insert = '';
		$this->unshift(null, '');
		while ($loop->counter < $loop->to) {
			$loop->counter ++;
			if ($loop->counter >= $loop->from) {
				if ($loop->first) {
					$loop->first = false;
				}
				else {
					$loop_insert .= $this->parseVars($loop->separator);
				}
				$sub_content = $this->parseVars($loop->content);
				$loop_insert .= $sub_content;
			}
		}
		$this->shift();
		return $loop_insert;
	}

	//----------------------------------------------------------------------------------- parseLoopId
	/**
	 * Removes <!--id--> code from a loop content
	 *
	 * @todo HIGH see what it is used for (found only for typed_address : maybe should be removed)
	 */
	protected function parseLoopId(Loop $loop) : void
	{
		foreach (['content', 'else_content'] as $property) {
			if (
				(($i = strrpos($loop->$property, '<!--id-->')) !== false)
				// patched : only if odd count if <!--id-->. If not, it is a real loop and not a 'has_id'
				&& (substr_count($loop->$property, '<!--id-->') % 2)
			) {
				$loop->$property = substr($loop->$property, 0, $i) . substr($loop->$property, $i + 9);
				$loop->has_id    = true;
			}
		}
	}

	//---------------------------------------------------------------------------- parseLoopSearchEnd
	/**
	 * Search '<!--end-->' into content, starting from $position.
	 * Recurse into '<!--other_things-->' and their matching '<!--end-->' if there are some
	 *
	 * @param $content  string  The content of the template
	 * @param $position integer The position of the '-->' of the start of the current loop
	 * @return integer[] the position of the '<!--else-->' then of '<!--end-->' of the current loop
	 */
	protected function parseLoopSearchEnd(string $content, int $position) : array
	{
		$recurse = 0;
		while ($position = strpos($content, '<!--', $position)) {
			$position += 4;
			if (substr($content, $position, 6) === 'end-->') {
				if ($recurse) {
					$recurse --;
				}
				else {
					$end_position = $position - 4;
					if (!isset($else_position)) {
						$else_position = $end_position;
					}
					return [$else_position, $end_position];
				}
			}
			elseif (substr($content, $position, 7) === 'else-->') {
				if (!$recurse) {
					$else_position = $position - 4;
				}
			}
			elseif (
				(substr($content, $position, 5) !== 'id-->')
				&& (substr($content, $position, 9) !== 'sample-->')
				&& (substr($content, $position, 4) !== 'use ')
				&& $this->parseThis($content, $position)
			) {
				$recurse ++;
			}
		}
		trigger_error('Missing <!--end--> into template', E_USER_WARNING);
		$end_position = strlen($content);
		if (!isset($else_position)) {
			$else_position = $position;
		}
		return [$else_position, $end_position];
	}

	//------------------------------------------------------------------------------ parseLoopVarName
	/**
	 * @param $loop    Loop
	 * @param $content string
	 * @param $else_j  ?integer the position of <!--else-->
	 * @param $end_j   integer  the position of <!--end-->
	 * @return integer the length of the end tag var name
	 */
	protected function parseLoopVarName(Loop $loop, string $content, ?int &$else_j, int &$end_j) : int
	{
		$search_var_name = $loop->var_name;

		if (str_ends_with($loop->var_name, '>')) {
			$end_last       = true;
			$loop->var_name = substr($loop->var_name, 0, -1);
		}

		while (($k = strpos($loop->var_name, '{')) !== false) {
			$l = strpos($loop->var_name, '}');
			$this->parseVar($loop->var_name, $k + 1, $l);
		}

		$loop->force_equality = ($loop->var_name[0] === '=');
		if ($loop->force_equality) {
			$loop->var_name = substr($loop->var_name, 1);
		}

		$loop->force_condition = str_ends_with($loop->var_name, '?');
		if ($loop->force_condition) {
			$loop->var_name = substr($loop->var_name, 0, -1);
		}

		if (str_contains($loop->var_name, ':')) {
			[$loop->var_name, $loop->has_expr] = explode(':', $loop->var_name);
			$search_var_name                   = lParse($search_var_name, ':');
			if (($sep = strpos($loop->has_expr, '-')) !== false) {
				$loop->from = substr($loop->has_expr, 0, $sep);
				$loop->to   = substr($loop->has_expr, $sep + 1);
			}
			else {
				$loop->from = $loop->to = $loop->has_expr;
			}
			$loop->to = (($loop->to === '') ? null : $loop->to);
		}
		else {
			$loop->from = 0;
			$loop->to   = null;
		}

		if ($loop->use_end) {
			$length2 = 3;
			[$else_j, $end_j] = $this->parseLoopSearchEnd($content, $end_j);
		}
		else {
			$length2         = strlen($search_var_name);
			$else_j = $end_j = isset($end_last)
				? strrpos($content, '<!--' . $search_var_name . '-->', $end_j + 3)
				: strpos($content, '<!--' . $search_var_name . '-->', $end_j + 3);
		}

		return $length2;
	}

	//------------------------------------------------------------------------------------ parseLoops
	/**
	 * Parse all loops and conditions from the template
	 *
	 * @example parsed conditions will have those forms :
	 *   <!--variable_name-->(...)<!--variable_name-->
	 *   <!--methodName()-->(...)<!--methodName()-->
	 *   <!--@function-->(...)<!--@function-->
	 * @return string updated content
	 */
	protected function parseLoops(string $content) : string
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
				$i_content = $this->parseLoop($content, $i, $j);
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
	protected function parseMap(Reflection_Property $property, array $collection) : string
	{
		return (new Html\Builder\Map($property, $collection))->build();
	}

	//----------------------------------------------------------------------------------- parseMethod
	protected function parseMethod(object $object, string $property_name) : mixed
	{
		if ($i = strpos($property_name, '(')) {
			$method_name = substr($property_name, 0, $i);
			$i ++;
			$j      = strpos($property_name, ')', $i);
			$params = $this->parseFuncParams(substr($property_name, $i, $j - $i));
			return call_user_func_array([$object, $method_name], $params);
		}
		return $object->$property_name();
	}

	//--------------------------------------------------------------------------------- parseNavigate
	/**
	 * Navigate into $var_name through parents (-) and children (+)
	 *
	 * @return string new $var_name (without -/+)
	 */
	protected function parseNavigate(string $var_name) : string
	{
		while ($var_name[0] === '-') {
			[$descendant_name, $descendant] = $this->shift();
			array_unshift($this->descendants_names, $descendant_name);
			array_unshift($this->descendants,       $descendant);
			$var_name = substr($var_name, 1);
		}
		while ($var_name[0] === '+') {
			$this->unshift(array_shift($this->descendants_names), array_shift($this->descendants));
			$var_name = substr($var_name, 1);
		}
		return $var_name;
	}

	//-------------------------------------------------------------------------------------- parseNot
	/** Returns the reverse boolean value for property value */
	protected function parseNot(string $property_name) : bool
	{
		return !$this->parseValue(substr($property_name, 1));
	}

	//--------------------------------------------------------------------------- parseObjectToString
	protected function parseObjectToString(
		/** @noinspection PhpUnusedParameterInspection */
		object $object, string $property_name
	) : string
	{
		return method_exists($object, '__toString') ? strval($object) : '';
	}

	//-------------------------------------------------------------------------------- parseParameter
	protected function parseParameter(string $parameter_name) : mixed
	{
		return $this->parameters[$parameter_name] ?? '';
	}

	//----------------------------------------------------------------------------------- parseParent
	protected function parseParent() : mixed
	{
		$this->shift();
		return reset($this->objects);
	}

	//------------------------------------------------------------------------------------- parsePath
	/**
	 * Parse current object through methods/properties path
	 *
	 * @return array [mixed $object, string $property_name]
	 */
	protected function parsePath(string $var_name) : array
	{
		$object        = null;
		$parenthesis   = '';
		$property_name = '';
		foreach (explode(DOT, $var_name) as $property_name) {
			if ($parenthesis) {
				$property_name = $parenthesis . DOT . $property_name;
				$parenthesis   = '';
			}
			if (
				str_contains($property_name, '(')
				&& (substr_count($property_name, '(') > substr_count($property_name, ')'))
			) {
				$parenthesis = $property_name;
			}
			else {
				$object = $this->parseSingleValue($property_name);
				if ($property_name !== '') {
					$this->unshift($property_name, $object);
				}
			}
		}
		return [$object, $property_name];
	}

	//--------------------------------------------------------------------------------- parseProperty
	protected function parseProperty(
		object $object, string $property_name, bool $ignore_unknown_property = false
	) : mixed
	{
		$class_name = get_class($object);
		if ($property_name === 'id') {
			return $object->$property_name ?? null;
		}
		if (property_exists($class_name, $property_name)) {
			/** @noinspection PhpUnhandledExceptionInspection property exists */
			$property = new Reflection_Property($class_name, $property_name);
			$getter   = $property->getAnnotation('user_getter')->value;
			if ($getter) {
				$callable = new Contextual_Callable($getter, $object);
				return $callable->call($property);
			}
		}
		if ($ignore_unknown_property) {
			$value = (
				property_exists($object, $property_name)
				|| property_exists($object, $property_name . '_')
				|| isset($object->$property_name)
			)
				? $object->$property_name
				: null;
		}
		else {
			$value = $object->$property_name;
		}
		if (
			(is_string($value) || (is_object($value) && method_exists($value, '__toString')))
			&& str_contains($value, '|')
		) {
			$value = str_replace('|', '&#124;', $value);
		}
		return $value;
	}

	//-------------------------------------------------------------------------------- parseSeparator
	/**
	 * Removes <!--separator-->(...) code from a loop content, and returns the separator content.
	 *
	 * @param $loop Loop
	 * @return string the separator content
	 */
	protected function parseSeparator(Loop $loop) : string
	{
		if (($i = strrpos($loop->content, '<!--separator-->')) !== false) {
			$separator = substr($loop->content, $i + 16);
			// this separator is not for me if there is any <!--block--> to parse into its source code.
			$j = 0;
			while (strpos($separator, '<!--', $j) !== false) {
				$j += 4;
				if ($this->parseThis($separator, $j)) {
					return '';
				}
				$j = strpos($separator, '-->', $j) + 3;
			}
			// nothing to parse inside it ? This separator is for me.
			$loop->content = substr($loop->content, 0, $i);
			return $separator;
		}
		return '';
	}

	//------------------------------------------------------------------------------ parseSingleValue
	protected function parseSingleValue(string $property_name, bool $format_value = true) : mixed
	{
		$add_div       = false;
		$source_object = $object = reset($this->objects);
		if (str_starts_with($property_name, '~')) {
			$ignore_undefined_property = true;
			$property_name             = substr($property_name, 1);
		}
		else {
			$ignore_undefined_property = false;
		}
		if (!strlen($property_name)) {
			$object = $this->parseParent();
		}
		elseif (is_numeric($property_name) && is_string($object)) {
			$object = substr($object, $property_name, 1);
		}
		elseif ($property_name === '#') {
			$object = reset($this->var_names);
		}
		elseif (str_contains($property_name, '?')) {
			$object = $this->parseConditional($property_name);
		}
		elseif (
			(str_starts_with($property_name, Q) && str_ends_with($property_name, Q))
			|| (str_starts_with($property_name, DQ) && str_ends_with($property_name, DQ))
		) {
			$object = $this->parseConstant($property_name);
		}
		elseif ($this->parse_class_name) {
			if ($property_name === 'class') {
				$object = $this->parse_class_name;
			}
			elseif (method_exists($this->parse_class_name, $property_name)) {
				$object = $this->parseStaticMethod($this->parse_class_name, $property_name);
			}
			elseif (property_exists($this->parse_class_name, $property_name)) {
				$object = $this->parseStaticProperty($this->parse_class_name, $property_name);
			}
			elseif (defined($this->parse_class_name . '::' . $property_name)) {
				$object = constant($this->parse_class_name . '::' . $property_name);
			}
			else {
				$object = isA($this->parse_class_name, $this->parseClassName($property_name));
			}
			$this->parse_class_name = '';
		}
		elseif (($property_name[0] >= 'A') && ($property_name[0] <= 'Z')) {
			if (is_array($object) && (isset($object[$property_name]) || !class_exists($property_name))) {
				$object = $this->parseArrayElement($object, $property_name);
			}
			elseif (
				(strlen($property_name) > 1) && (
					(($property_name[1] >= 'a') && ($property_name[1] <= 'z'))
					|| str_contains($property_name, BS)
				)
			) {
				$this->parse_class_name = $this->parseClassName($property_name);
				if (!class_exists($this->parse_class_name)) {
					trigger_error("Unknown class $this->parse_class_name", E_USER_WARNING);
				}
			}
			else {
				$object = $this->parseConst($object, $property_name);
			}
		}
		elseif ($property_name[0] === AT) {
			$object = $this->parseFunc(substr($property_name, 1));
		}
		elseif (substr($property_name, 0, 2) === '§') {
			$object = $this->parseParameter(substr($property_name, 2));
		}
		elseif ($i = strpos($property_name, '(')) {
			if (
				(is_object($object) || (!empty($object) && ctype_upper($object[0])))
				&& method_exists($object, substr($property_name, 0, $i))
			) {
				$object = $this->parseMethod($object, $property_name);
			}
			else {
				$object = $this->callFunc(reset($this->objects), $property_name);
			}
		}
		elseif (is_array($object) && isset($object[$property_name])) {
			$object = $this->parseArrayElement($object, $property_name);
		}
		elseif (!is_object($object) && !array_key_exists($property_name, $this->parameters)) {
			$object = $this->parseString(strval($object), $property_name);
		}
		elseif (
			(is_object($object) || (is_string($object) && !empty($object) && ctype_upper($object[0])))
			&& method_exists($object, $property_name)
		) {
			/** @var $object Reflection_Property|mixed */
			$is_property_value = ($property_name === 'value') && ($object instanceof Reflection_Property);
			if (
				$is_property_value
				&& ($builder = Widget::of($object)?->class_name)
				&& is_a($builder, Html\Builder\Property::class, true)
			) {
				/** @noinspection PhpUnhandledExceptionInspection widget builder must be valid */
				/** @var $builder Html\Builder\Property */
				$builder = Builder::create(
					$builder, [$object, $this->parseMethod($object, $property_name), $this]
				);
				$value = $builder->buildHtml();
				if ($value === static::ORIGIN) {
					$add_div = true;
				}
				else {
					$format_value = false;
					$object       = $value;
				}
			}
			else {
				$value = static::ORIGIN;
			}
			if ($value === static::ORIGIN) {
				$property = $object;
				if ($is_property_value && $this->link_objects) {
					$object   = $this->parseMethod($object, $property_name);
					$type     = $property->getType();
					/** @noinspection PhpUnhandledExceptionInspection is_object */
					if (
						is_object($object)
						&& !($object instanceof Stringable)
						&& $type->isSingleClass()
						&& Store::of(new Reflection_Class($object))->value
						&& Dao::getObjectIdentifier($object)
					) {
						$anchor = new Anchor(Framework\View::link($object), strval($object));
						$anchor->setAttribute('target', Target::MAIN);
						$object = strval($anchor);
					}
					elseif (($object instanceof File) && Dao::getObjectIdentifier($object)) {
						$object = $this->parseFileToString($object);
					}
				}
				else {
					try {
						$object = $this->parseMethod($object, $property_name);
					}
					catch (Error $exception) {
						$object = Loc::tr('undefined');
						if (
							!$ignore_undefined_property
							|| !str_contains(
								$exception->getMessage(), 'Cannot access uninitialized non-nullable property'
							)
						) {
							throw $exception;
						}
					}
				}
			}
		}
		elseif (isset($object->$property_name)) {
			$object = $this->parseProperty($object, $property_name, $ignore_undefined_property);
		}
		elseif (array_key_exists($property_name, $this->parameters)) {
			$object = $this->parseParameter($property_name);
		}
		else {
			$object = $this->parseProperty($object, $property_name, $ignore_undefined_property);
		}
		if (
			$format_value
			&& ($source_object instanceof Reflection_Property)
			&& ($property_name === 'value')
		) {
			$object = (new Reflection_Property_View($source_object))->formatValue($object);
		}
		if ($add_div && isset($property) && ($property instanceof Reflection_Property)) {
			$object = $this->originValueAddDiv($object, $property);
		}
		return $object;
	}

	//----------------------------------------------------------------------------- parseStaticMethod
	protected function parseStaticMethod(string $class_name, string $method_name) : mixed
	{
		return $class_name::$method_name();
	}

	//--------------------------------------------------------------------------- parseStaticProperty
	/**
	 * Returns the value of the static property (if static), otherwise the property itself is returned
	 *
	 * @noinspection PhpMixedReturnTypeCanBeReducedInspection No : $class_name::$$property_name
	 */
	protected function parseStaticProperty(string $class_name, string $property_name) : mixed
	{
		/** @noinspection PhpUnhandledExceptionInspection must exist */
		$property = (new Reflection_Property($class_name, $property_name));
		$value    = $property->isStatic() ? $class_name::$$property_name : $property;
		if (
			(is_string($value) || (is_object($value) && method_exists($value, '__toString')))
			&& str_contains($value, '|')
		) {
			$value = str_replace('|', '&#124;', $value);
		}
		return $value;
	}

	//----------------------------------------------------------------------------------- parseString
	/**
	 * If property name is the name of a String_Class method, call this method
	 * If not, will return true if string value equals $property_name
	 */
	protected function parseString(string $string, string $property_name) : mixed
	{
		$string = new String_Class($string);
		if (method_exists($string, $property_name)) {
			return $this->parseStringMethod($string, $property_name);
		}
		elseif (property_exists($string, $property_name)) {
			return $this->parseStringProperty($string, $property_name);
		}
		return ($string->value === $property_name);
	}

	//----------------------------------------------------------------------------- parseStringMethod
	protected function parseStringMethod(object $object, string $method_name) : mixed
	{
		return $object->$method_name();
	}

	//--------------------------------------------------------------------------- parseStringProperty
	protected function parseStringProperty(object $object, string $property_name) : mixed
	{
		return $object->$property_name ?? null;
	}

	//------------------------------------------------------------------------------------- parseThis
	/** Return true if text at position $i of $content is variable, or function name to include */
	protected function parseThis(string $content, int $i) : bool
	{
		$c = $content[$i];
		return ctype_lower($c)
			|| (
				ctype_upper($c)
				&& (substr($content, $i, 6) !== 'BEGIN:') && (substr($content, $i, 4) !== 'END:')
			)
			|| str_contains('#@§/.-+?!~|="' . Q, $c);
	}

	//-------------------------------------------------------------------------------------- parseUse
	protected function parseUse(string &$content, int $i, int $j) : void
	{
		$class_name = substr($content, $i + 4, $j - $i - 4);
		$this->use[Namespaces::shortClassName($class_name)] = $class_name;
		$content = substr($content, 0, $i - 4) . substr($content, $j + 3);
	}

	//------------------------------------------------------------------------------------ parseValue
	/**
	 * Parse a variable / function / include and returns its return value
	 *
	 * @param $var_name  string  can be a unique var or path.of.vars
	 * @param $as_string boolean if true, returned value will always be a string
	 * @return mixed var value after reading value / executing specs
	 */
	public function parseValue(string $var_name, bool $as_string = false) : mixed
	{
		if ($var_name === DOT) {
			return reset($this->objects);
		}
		elseif ($var_name === '') {
			return '';
		}
		elseif ($var_name[0] === SL) {
			return $this->parseInclude($var_name);
		}
		elseif ($var_name[0] === '!') {
			$not      = true;
			$var_name = substr($var_name, 1);
		}
		if (str_ends_with($var_name, '*')) {
			$group    = true;
			$var_name = substr($var_name, 0, -1);
		}
		if (str_contains('-+', $var_name[0])) {
			$context     = $this->backupContext();
			$descendants = $this->backupDescendants();
			$var_name    = $this->parseNavigate($var_name);
		}
		$property_name = null;
		if ($var_name === DOT) {
			$object = reset($this->objects);
		}
		elseif (str_contains($var_name, DOT)) {
			if (!isset($context)) {
				$context = $this->backupContext();
			}
			[$object, $property_name] = $this->parsePath($var_name);
		}
		else {
			$object = $this->parseSingleValue($var_name);
		}
		// if the parse value finishes with a class name : check if the last object is any of this class
		if ($this->parse_class_name) {
			$object                 = isA(reset($this->objects), $this->parse_class_name);
			$this->parse_class_name = '';
		}
		// parse object to string
		if ($as_string && is_object($object)) {
			if ($object instanceof File) {
				$object = $this->parseFileToString($object);
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
		if (isset($context)) {
			$this->restoreContext($context);
		}
		if (isset($descendants)) {
			$this->restoreDescendants($descendants);
		}
		if (isset($group)) {
			$object = $this->group($var_name, $object);
		}
		// if value contains translated data (|), do not translate if | is alone
		if (
			(is_string($object) || (is_object($object) && method_exists($object, '__toString')))
			&& (substr_count($object, '|') % 2)
		) {
			$object = str_replace('|', '&#124;', $object);
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------- parseVar
	protected function parseVar(string &$content, int $i, int $j, bool $html_entities = false) : int
	{
		$var_name = substr($content, $i, $j - $i);
		while (($k = strpos($var_name, '{')) !== false) {
			$this->parseVar($content, $k + $i + 1, $j);
			$j        = strpos($content, '}', $i);
			$var_name = substr($content, $i, $j - $i);
		}
		$auto_remove = $this->parseVarWillAutoRemove($var_name);
		$value       = $this->parseValue($var_name);
		$object      = reset($this->objects);
		if (
			is_array($value)
			&& ($object instanceof Reflection_Property)
			&& $object->getType()->isClass()
		) {
			$value = Link_Annotation::of($object)->isCollection()
				? $this->parseCollection($object, $value)
				: $this->parseMap($object, $value);
		}
		$i --;
		if (is_array($value)) {
			$value = $value ? join(', ', $value) : '';
		}
		if ($auto_remove && (is_null($value) || !strlen($value))) {
			$this->parseVarRemove($content, $i, $j);
		}
		if ($html_entities && ($var_name[0] !== SL) && !($value instanceof No_Escape)) {
			$value = $this->htmlEntities(strval($value));
		}
		$content = substr($content, 0, $i) . $value . substr($content, $j + 1);
		$i      += strlen($value);
		return $i;
	}

	//-------------------------------------------------------------------------------- parseVarRemove
	protected function parseVarRemove(string $content, int &$i, int &$j) : void
	{
		if (
			(($content[$i - 1] === Q) && ($content[$j + 1] === Q))
			|| (($content[$i - 1] === DQ) && ($content[$j + 1] === DQ))
			|| (($content[$i - 1] === '|') && ($content[$j + 1] === '|'))
		) {
			$i --;
			$j ++;
		}
		// <element>{?something}</element>
		if ($content[$i - 1] === '>') {
			while ($content[$i] !== '<') {
				$i --;
			}
			while ($content[$j] !== '>') {
				$j ++;
			}
			return;
		}
		while (!in_array($content[$i], [SL, SP, ','], true)) {
			if (($content[$i] === Q) || ($content[$i] === DQ)) {
				while ($content[$j] !== $content[$i]) {
					$j ++;
				}
			}
			$i --;
		}
	}

	//------------------------------------------------------------------------ parseVarWillAutoRemove
	protected function parseVarWillAutoRemove(string &$var_name) : bool
	{
		if ($var_name[0] === '?') {
			$var_name = substr($var_name, 1);
			return true;
		}
		return false;
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
	 * @return string updated content
	 */
	public function parseVars(string $content) : string
	{
		$content = $this->parseLoops($content);
		$i       = 0;
		while (($i = strpos($content, '{', $i)) !== false) {
			$i ++;
			if ($this->parseThis($content, $i)) {
				$j = strpos($content, '}', $i);
				$i = $this->parseVar($content, $i, $j, true);
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- prepareW3Links
	protected function prepareW3Links(string $content) : string
	{
		foreach (['abs://', 'app://', 'dyn://', 'rel://', 'id="'] as $protocol) {
			$i = 0;
			while ($i = strpos($content, $protocol, $i)) {
				$delimiter = str_starts_with($protocol, 'id=') ? $content[$i + 3] : $content[$i - 1];
				$i        += 4;
				$j         = strpos($content, $delimiter, $i);
				$i2        = $i;
				while (($i2 = strpos($content, '(', $i2)) && ($i2 < $j)) {
					if (($content[$i2 + 1] === Q) && ($content[strpos($content, ')', $i2) - 1] === Q)) {
						$i2 ++;
					}
					else {
						$content[$i2] = '{';
					}
				}
				$i2 = $i;
				while (($i2 = strpos($content, ')', $i2)) && ($i2 < $j)) {
					if ($content[$i2 - 1] === Q) {
						$i2 ++;
					}
					else {
						$content[$i2] = '}';
					}
				}
				$i = $j;
			}
		}
		return $content;
	}

	//--------------------------------------------------------------------------- removeAppAttributes
	protected function removeAppAttributes(string $content) : string
	{
		$i = 0;
		while (($i = strpos($content, 'data-attribute-', $i)) !== false) {
			$content = substr($content, 0, $i) . substr($content, $i + 15);
		}

		$i = 0;
		while (($i = strpos($content, 'data-attributes=' . DQ, $i)) !== false) {
			$i      += 17;
			$j       = strpos($content, DQ, $i);
			$content = substr($content, 0, $i - 17)
				. substr($content, $i, $j - $i)
				. substr($content, $j + 1);
		}

		$i            = 0;
		$replacements = false;
		while (($i = strpos($content, ' data-begin="{', $i)) !== false) {
			$i      += 14;
			$j       = strpos($content, '}' . DQ, $i);
			$content = substr($content, 0, $i - 14)
				. '<!--' . substr($content, $i, $j - $i) . '-->'
				. substr($content, $j + (($content[$j + 2] === SP) ? 2 : 1));
			$replacements = true;
		}
		if ($replacements) {
			$content = str_replace(' data-end', '<!--end-->', $content);
		}

		$i = 0;
		while (($i = strpos($content, ' data-each="{', $i)) !== false) {
			$i += 13;
			$j  = strpos($content, '}' . DQ, $i);
			$content = substr($content, 0, $i - 13)
				. '<!--' . substr($content, $i, $j - $i) . '--> data-{@key}="{.}"<!--end-->'
				. substr($content, $j + 2);
		}

		$i = 0;
		while (($i = strpos($content, '<!--DEV-->', $i)) !== false) {
			$j       = strpos($content, '<!--END-->', $i) + 10;
			$content = substr($content, 0, $i) . substr($content, $j);
		}

		return $content;
	}

	//-------------------------------------------------------------------------------- removeAppLinks
	/** @noinspection HttpUrlsUsage Always "or https" */
	protected function removeAppLinks(string $content) : string
	{
		$content = str_replace(['abs://http://', 'abs://https://'], ['http://', 'https://'], $content);
		$content = str_replace(['app://http://', 'app://https://'], ['http://', 'https://'], $content);
		$content = str_replace(['dyn://http://', 'dyn://https://'], ['http://', 'https://'], $content);
		$content = str_replace(['rel://http://', 'rel://https://'], ['http://', 'https://'], $content);
		$content = str_replace(['abs:///', 'abs://'], Paths::absoluteBase() . SL, $content);
		$content = str_replace(['app:///', 'app://'], SL, $content);
		$content = str_replace(['dyn:///', 'dyn://', 'rel:///', 'rel://'], '', $content);
		return     str_replace(["url('http://{", "url('https://{"], "url('{", $content);
	}

	//-------------------------------------------------------------------------------- removeComments
	protected function removeComments(string $content) : string
	{
		foreach (['//', '#'] as $comment_tag) {
			$i = 0;
			while (($i = strpos($content, '<!--' . $comment_tag, $i)) !== false) {
				$j       = strpos($content, '-->', $i) + 3;
				$content = substr($content, 0, $i) . substr($content, $j);
			}
		}
		return $content;
	}

	//---------------------------------------------------------------------------------- removeSample
	/** Remove <!--sample-->(...) code from loop content */
	protected function removeSample(Loop $loop) : void
	{
		foreach (['content', 'else_content'] as $property) {
			$i = strrpos($loop->$property, '<!--sample-->');
			if ($i !== false) {
				if (strpos($loop->$property, '<!--', $i + 1) === false) {
					$loop->$property = substr($loop->$property, 0, $i);
				}
			}
		}
	}

	//--------------------------------------------------------------------------- replaceHeadElements
	/**
	 * Replace elements <element> without closure </element> with their value
	 * The first attribute is used as element identifier
	 *
	 * @exemple if $elements = ['<link rel="canonical" href="http://www.mysite.fr">']
	 *          'link rel="canonical"...' will be replaced by the new value into the '<head>'
	 *          If did not exist, the element is added to the beginning of '<head>'
	 * @param $content  string Page content
	 * @param $elements string[]
	 */
	protected function replaceHeadElements(string &$content, array $elements) : void
	{
		if (($i = strpos($content, '<element')) !== false) {
			// remove already existing element
			foreach ($elements as $element_key => $element) {
				$search = substr($element, 0, strpos($element, ' ', strpos($element, '=')));
				while (($j = strpos($content, $search)) !== false) {
					$k = strpos($content, '>', $j) + 1;
					while (in_array($content[$k], [SP, CR, LF, TAB])) {
						$k ++;
					}
					$content = substr($content, 0, $j) . substr($content, $k);
				}
				if (str_contains($element, '=' . DQ . DQ)) {
					unset($elements[$element_key]);
				}
			}
			// add elements
			$content = substr($content, 0, $i) . join("\n\t", $elements) . "\n\t"
				. substr($content, $i);
		}
		elseif (($i = strpos($content, '<head')) !== false) {
			$j            = strpos($content, '</head>', $i);
			$head_content = substr($content, $i, $j - $i);
			foreach ($elements as $element_key => $element) {
				if (str_contains($element, '=' . DQ . DQ) || str_contains($head_content, $element)) {
					unset($elements[$element_key]);
				}
			}
			$i       = strpos($content, '>', $i) + 1;
			$content = substr($content, 0, $i) . "\n\t" . join("\n\t", $elements)
				. substr($content, $i);
		}
	}

	//------------------------------------------------------------------------------ replaceHeadLinks
	/**
	 * @param $content string
	 * @param $links   string[]
	 */
	protected function replaceHeadLinks(string &$content, array $links) : void
	{
		if ($links) {
			$this->replaceHeadElements($content, $links);
		}
	}

	//------------------------------------------------------------------------------ replaceHeadMetas
	/**
	 * @param $content string
	 * @param $metas   string[]
	 */
	protected function replaceHeadMetas(string &$content, array $metas) : void
	{
		if ($metas) {
			$this->replaceHeadElements($content, $metas);
		}
	}

	//------------------------------------------------------------------------------ replaceHeadTitle
	/**
	 * @param $content string
	 * @param $title   string title, including '<title>' and '</title>' delimiters
	 */
	protected function replaceHeadTitle(string &$content, string $title) : void
	{
		if (!$title) {
			return;
		}
		if (($i = strpos($content, '<title')) !== false) {
			$j       = strpos($content, '</title>', $i) + 8;
			$content = substr($content, 0, $i) . $title . substr($content, $j);
		}
		elseif (($i = strpos($content, '<head')) !== false) {
			$i       = strpos($content, '>', $i) + 1;
			$content = substr($content, 0, $i) . "\n\t" . $title . substr($content, $i);
		}
	}

	//----------------------------------------------------------------------------------- replaceLink
	/** Replace link with correct link path. Commonly called for action=, link href=, location= */
	protected function replaceLink(string $link) : string
	{
		if (Uri::startsWithProtocol($link)) {
			return str_starts_with($link, SL) ? substr($link, 1) : $link;
		}
		$base = isset($this->parameters[static::ABSOLUTE_LINKS])
			? Paths::absoluteBase()
			: ($this->getUriRoot() . $this->getScriptName());
		$full_path = str_replace([SL . SL, SL . DOT . SL], SL, $base . $link);
		if (str_starts_with($full_path, DOT . SL)) {
			$full_path = substr($full_path, 2);
		}
		return $full_path;
	}

	//---------------------------------------------------------------------------------- replaceLinks
	/**
	 * Replace links with correct absolute paths into $content
	 * Commonly called for a href=, action=, location=
	 *
	 * @return string updated content
	 */
	protected function replaceLinks(string $content) : string
	{
		$black_zones = $this->blackZonesOf($content, ['<textarea' => '</textarea>']);
		$length      = strlen($content);
		$links       = ['action=', 'href=', 'location='];
		foreach ($links as $l) {
			foreach ([DQ, Q] as $quote) {
				$link        = $l . $quote;
				$link_length = strlen($link);
				$i           = 0;
				while (($i = strpos($content, $link, $i)) !== false) {
					if ($l === 'href=') {
						$of = strrpos($content, '<', $i - $length);
						$ok = (substr($content, $of, 6) !== '<link ');
					}
					else {
						$ok = true;
					}
					$i += $link_length;
					$j = strpos($content, $quote, $i);
					if (
						!$ok || (substr($content, $i,
								1) !== SL) || $this->isInBlackZones($black_zones, $i)
					) {
						$i = $j;
					}
					else {
						$replacement_uri = $this->replaceLink(substr($content, $i, $j - $i));
						$content         = substr($content, 0, $i) . $replacement_uri
							. substr($content, $j);
						$length          = strlen($content);
						$this->blackZonesInc(
							$black_zones,
							strlen($replacement_uri) - ($j - $i),
							$j
						);
						$i += strlen($replacement_uri);
					}
				}
			}
		}
		return $content;
	}

	//------------------------------------------------------------------------------------ replaceUri
	/**
	 * Replace URI with correct URI path
	 * Commonly called for @import, link href=, src=, loadScript( of gif, jpg, png, css, js files
	 *
	 * @return string updated uri
	 */
	protected function replaceUri(string $uri) : string
	{
		if (Uri::startsWithProtocol($uri)) {
			return str_starts_with($uri, SL) ? substr($uri, 1) : $uri;
		}
		if (
			in_array(rLastParse($uri, DOT), ['gif', 'jpg', 'png'])
			&& file_exists(Paths::$file_root . SL . $uri)
		) {
			return $uri;
		}
		$position  = strrpos($uri, '/vendor/');
		$file_name = ($position !== false)
			? substr($uri, $position + 1)
			: substr($uri, strrpos($uri, SL) + 1);
		$file_path = null;
		if (str_ends_with($file_name, '.css')) {
			$file_path = static::getCssPath($this->css) . SL . $file_name;
			if (!file_exists(Paths::$file_root . $file_path)) {
				$file_path = null;
			}
		}
		if ($file_name && !isset($file_path)) {
			$file_path = substr(
				stream_resolve_include_path($file_name), strlen(realpath(Paths::$file_root)) + 1
			);
			if (!$file_path || !file_exists(Paths::$file_root . $file_path)) {
				return $this->replaceLink(SL . $uri);
			}
		}
		if (is_file(Paths::$file_root . $file_path)) {
			$file_path .= '?' . md5_file(Paths::$file_root . $file_path);
		}
		return $file_path ? ($this->getUriRoot() . $file_path) : '';
	}

	//----------------------------------------------------------------------------------- replaceUris
	/**
	 * Replace URIs with correct URIs paths into $content
	 * Commonly called for @import, link href=, src=, loadScript( of gif, jpg, png, css, js files
	 *
	 * @return string updated content
	 */
	protected function replaceUris(string $content) : string
	{
		$black_zones = $this->blackZonesOf($content, ['<textarea' => '</textarea>']);
		$length      = strlen($content);
		$links       = ['@import ', 'href=', 'src=', 'loadScript('];
		foreach ($links as $l) {
			foreach ([DQ, Q] as $quote) {
				$link        = $l . $quote;
				$link_length = strlen($link);
				$i           = 0;
				while (($i = strpos($content, $link, $i)) !== false) {
					if ($l === 'href=') {
						$of = strrpos($content, '<', $i - $length);
						$ok = (substr($content, $of, 6) === '<link ');
					}
					else {
						$ok = true;
					}
					$i += $link_length;
					$j = strpos($content, $quote, $i);
					if (!$ok || $this->isInBlackZones($black_zones, $i)) {
						$i = $j;
					}
					else {
						$replacement_uri = $this->replaceUri(substr($content, $i, $j - $i));
						$content         = substr($content, 0, $i) . $replacement_uri
							. substr($content, $j);
						$length          = strlen($content);
						$this->blackZonesInc(
							$black_zones,
							strlen($replacement_uri) - ($j - $i),
							$j
						);
						$i += strlen($replacement_uri);
					}
				}
			}
		}
		return $content;
	}

	//-------------------------------------------------------------------------------- restoreContext
	/**
	 * @param $context array [string[], array, string[]]
	 *                       [$var_names, $objects, $translation_contexts]
	 * @see backupContext(), parseValue()
	 */
	protected function restoreContext(array $context) : void
	{
		[$this->var_names, $this->objects, Loc::$contexts_stack] = $context;
	}

	//---------------------------------------------------------------------------- restoreDescendants
	/**
	 * @param $descendants array [string[], array] [$descendants_names, $descendants]
	 * @see backupDescendants(), parseValue()
	 */
	protected function restoreDescendants(array $descendants) : void
	{
		[$this->descendants_names, $this->descendants] = $descendants;
	}

	//------------------------------------------------------------------------------------ setContent
	public function setContent(string $content) : void
	{
		$this->content = $content;
	}

	//---------------------------------------------------------------------------------------- setCss
	public function setCss(string $css) : void
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
	 * @param $parameters array key is parameter name
	 */
	public function setParameters(array $parameters) : void
	{
		if (isset($parameters[Parameter::IS_INCLUDED])) {
			$parameters[Parameter::AS_WIDGET] = true;
		}
		$this->parameters = $parameters;
		// functions may depend on parameters, so it could not be initialised before here
		$this->functions = $this->newFunctions();
	}

	//----------------------------------------------------------------------------------------- shift
	/**
	 * @return array [string, mixed]
	 */
	protected function shift() : array
	{
		$var_name = array_shift($this->var_names);
		$object   = array_shift($this->objects);
		if (is_object($object)) {
			Loc::exitContext();
		}
		return [$var_name, $object];
	}

	//--------------------------------------------------------------------------------------- unshift
	protected function unshift(string $var_name, mixed $object) : void
	{
		if (is_object($object)) {
			Loc::enterContext(get_class($object));
		}
		array_unshift($this->var_names, $var_name);
		array_unshift($this->objects,   $object);
	}

}
