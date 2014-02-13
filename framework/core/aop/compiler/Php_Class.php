<?php
namespace SAF\AOP;

use ReflectionClass;

/**
 * Object representation of a Php class read from source
 */
class Php_Class
{

	//------------------------------------------------------------------------------------- $abstract
	/**
	 * @var string 'abstract' or null
	 */
	public $abstract;

	//---------------------------------------------------------------------------------------- $clean
	/**
	 * @var boolean true if file was clean before cleanup
	 */
	public $clean;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------- $documentation
	/**
	 * @var string
	 */
	public $documentation;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//-------------------------------------------------------------------------------------- $imports
	/**
	 * @var string[] full class names or namespaces
	 */
	private $imports;

	//--------------------------------------------------------------------------------------- $indent
	/**
	 * @var string
	 */
	public $indent;

	//---------------------------------------------------------------------------- $inherited_methods
	/**
	 * @var Php_Method[]
	 */
	private $inherited_methods;

	//------------------------------------------------------------------------- $inherited_properties
	/**
	 * @var Php_Property[]
	 */
	private $inherited_properties;

	//----------------------------------------------------------------------------------- $interfaces
	/**
	 * @var Php_Class[]|string[]
	 */
	private $interfaces;

	//------------------------------------------------------------------------------------- $internal
	/**
	 * @var boolean
	 */
	public $internal = false;

	//-------------------------------------------------------------------------------------- $methods
	/**
	 * @var Php_Method[]
	 */
	private $methods;

	//------------------------------------------------------------------------------------ $namespace
	/**
	 * @var string
	 */
	public $namespace;

	//--------------------------------------------------------------------------------------- $parent
	/**
	 * @var Php_Class|string
	 */
	private $parent;

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var Php_Property[]
	 */
	private $properties;

	//------------------------------------------------------------------------------------- prototype
	/**
	 * @var string
	 */
	public $prototype;

	//--------------------------------------------------------------------------------------- $source
	/**
	 * @var string
	 */
	public $source;

	//------------------------------------------------------------------------------- $traits_methods
	/**
	 * @var Php_Method[]
	 */
	private $traits_methods;

	//---------------------------------------------------------------------------- $traits_properties
	/**
	 * @var Php_Property[]
	 */
	private $traits_properties;

	//----------------------------------------------------------------------------------------- $type
	/**
	 * @var string 'class', 'interface' or 'trait'
	 */
	public $type;

	//------------------------------------------------------------------------------------------ $use
	/**
	 * @var Php_Class[]
	 */
	private $traits;

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $buffer string
	 * @return boolean true if cleanup was necessary, false if buffer was clean before cleanup
	 */
	private static function cleanup(&$buffer)
	{
		// remove all "\r"
		$buffer = trim(str_replace("\r", '', $buffer));
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '%\n\s*//\#+\s+AOP.*%s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . ($match1 ? "\n\n}\n" : "\n");
		// replace "/* public */ private [static] function name_?(" by "public [static] function name("
		$expr = '%'
			. '(?:\n\s*/\*\*\s+@noinspection\s+PhpUnusedPrivateMethodInspection(?:\s+\w*)*\*/)?'
			. '(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)' // 1 2 3
			. '(?:(?:private|protected|public)\s+)?'
			. '(static\s+)?' // 4
			. 'function(\s+\w+)\_[0-9]*\s*' // 5
			. '\('
			. '%';
		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$4function$5(', $buffer);
		return $match1 || $match2;
	}

	//--------------------------------------------------------------------------------- fromClassName
	/**
	 * @param $class_name string
	 * @return Php_Class
	 */
	public static function fromClassName($class_name)
	{
		$reflection = new ReflectionClass($class_name);
		$filename = $reflection->getFileName();
		$class = $filename ? self::fromFile($filename) : self::fromReflection($reflection);
		if (!$class) {
			$class = self::fromReflection($reflection);
		}
		return $class;
	}

	//-------------------------------------------------------------------------------------- fromFile
	/**
	 * @param $file_name string
	 * @return Php_Class
	 */
	public static function fromFile($file_name)
	{
		$source = file_get_contents($file_name);
		$clean = !self::cleanup($source);
		preg_match(self::regex(), $source, $match);
		if ($match) {
			$class = self::fromMatch($match);
			$class->clean     = $clean;
			$class->file_name = $file_name;
			$class->source    = $source;
			return $class;
		}
		if (ctype_upper(rLastParse($file_name, '/')[0])) {
			trigger_error('No class in file ' . $file_name, E_USER_NOTICE);
		}
		return null;
	}

	//------------------------------------------------------------------------------------- fromMatch
	/**
	 * @param $match array
	 * @param $n     integer
	 * @return Php_Class
	 */
	public static function fromMatch($match, $n = null)
	{
		$class = new Php_Class();
		if (isset($n)) {
			$class->namespace     = $match[1][$n];
			$class->indent        = $match[2][$n];
			$class->documentation = $match[3][$n];
			$class->prototype     = $match[4][$n];
			$class->abstract      = empty($match[5][$n]) ? null : $match[5][$n];
			$class->type          = $match[6][$n];
			$class->name          = ($class->namespace ? $class->namespace . '\\' : '') . $match[7][$n];
			$class->parent        = empty($match[8][$n]) ? null : $match[8][$n];
			$class->interfaces    = empty($match[9][$n]) ? array() : self::parseImplements($match[8][$n]);
		}
		else {
			$class->namespace     = $match[1];
			$class->indent        = $match[2];
			$class->documentation = $match[3];
			$class->prototype     = $match[4];
			$class->abstract      = empty($match[5]) ? null : $match[5];
			$class->type          = $match[6];
			$class->name          = ($class->namespace ? $class->namespace . '\\' : '') . $match[7];
			$class->parent        = empty($match[8]) ? null : $match[8];
			$class->interfaces    = empty($match[9]) ? array() : self::parseImplements($match[9]);
		}
		class_exists($class->name);
		return $class;
	}

	//-------------------------------------------------------------------------------- fromReflection
	/**
	 * @param $reflection ReflectionClass
	 * @return Php_Class
	 */
	public static function fromReflection(ReflectionClass $reflection)
	{
		$class = new Php_Class();
		$class->abstract = $reflection->isAbstract() ? 'abstract' : null;
		$class->documentation = $reflection->getDocComment();
		$class->imports = array();
		$class->inherited_methods = array();
		$class->inherited_properties = array();
		$class->interfaces = array();
		foreach ($reflection->getInterfaces() as $interface) {
			$class->interfaces[$interface->name] = Php_Class::fromReflection($interface);
		}
		$class->internal = true;
		$class->methods = array();
		foreach ($reflection->getMethods() as $method) {
			$class->methods[$method->name] = Php_Method::fromReflection($class, $method);
		}
		$class->name = $reflection->name;
		$class->namespace = lLastParse($reflection->name, '\\', 1, false);
		$class->parent = false;
		$class->properties = array();
		foreach ($reflection->getProperties() as $property) {
			$class->properties[$property->name] = Php_Property::fromReflection($class, $property);
		}
		$class->traits = array();
		foreach ($reflection->getTraits() as $trait) {
			$class->traits[$trait->name] = Php_Class::fromReflection($trait);
		}
		$class->traits_methods = array();
		$class->traits_properties = array();
		$class->type = $reflection->isInterface() ? 'interface' : (
			$reflection->isTrait() ? 'trait' : 'class'
		);
		return $class;
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		return $this->abstract || ($this->type == 'interface') || ($this->type == 'trait');
	}

	//--------------------------------------------------------------------------------- getInterfaces
	/**
	 * @return Php_Class[]
	 */
	public function getInterfaces()
	{
		if ($this->interfaces && is_string(reset($this->interfaces))) {
			foreach ($this->interfaces as $key => $interface) {
				$this->interfaces[$key] = self::fromClassName(self::searchFullClassName($interface));
			}
		}
		return $this->interfaces;
	}

	//------------------------------------------------------------------------------------ getImports
	/**
	 * @return string[]
	 */
	public function getImports()
	{
		if (!isset($this->imports)) {
			$expr = '%'
				. '\n\s*use\s*'
				. '(?:([\\\\\w]+)(?:\s*\,\s*)?)+'
				. '\s*\;'
				. '%';
			$source = substr($this->source, 0, strpos($this->source, $this->prototype));
			preg_match_all($expr, $source, $match);
			$this->imports = $match ? $match[1] : array();
		}
		return $this->imports;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @param $flags string[] 'inherited', 'traits'
	 * @return Php_Method[]
	 */
	public function getMethods($flags = array())
	{
		if (!isset($this->methods)) {
			$this->methods = array();
			preg_match_all(Php_Method::regex(), $this->source, $match);
			foreach (array_keys($match[0]) as $n) {
				$method = Php_Method::fromMatch($this, $match, $n);
				$this->methods[$method->name] = $method;
			}
		}
		$methods = $this->methods;

		$flags = array_flip($flags);

		if (isset($flags['traits'])) {
			if (!isset($this->traits_methods)) {
				$this->traits_methods = array();
				foreach ($this->getTraits() as $trait) {
					$this->traits_methods = array_merge(
						$trait->getMethods(array('traits')), $this->traits_methods
					);
				}
			}
			$methods = array_merge($this->traits_methods, $methods);
		}

		if (isset($flags['inherited'])) {
			if (!isset($this->inherited_methods)) {
				$this->inherited_methods = array();
				foreach ($this->getInterfaces() as $interface) {
					$this->inherited_methods = array_merge(
						$interface->getMethods(array('inherited', 'traits')), $this->inherited_methods
					);
				}
				if ($parent = $this->getParent()) {
					$this->inherited_methods = array_merge(
						$parent->getMethods(array('inherited', 'traits')), $this->inherited_methods
					);
				}
			}
			$methods = array_merge($this->inherited_methods, $methods);
		}

		return $methods;
	}

	//------------------------------------------------------------------------------------- getParent
	/**
	 * @return Php_Class
	 */
	public function getParent()
	{
		if (is_string($this->parent)) {
			$this->parent = Php_Class::fromClassName($this->searchFullClassName($this->parent));
		}
		return $this->parent;
	}

	//--------------------------------------------------------------------------------- getProperties
	/**
	 * @param $flags string[] 'inherited', 'traits'
	 * @return Php_Property[]
	 */
	public function getProperties($flags = array())
	{
		if (!isset($this->properties)) {
			$this->properties = array();
			$regex = Php_Property::regex();
			preg_match_all($regex, $this->source, $match);
			foreach (array_keys($match[0]) as $n) {
				$property = Php_Property::fromMatch($this, $match, $n);
				$this->properties[$property->name] = $property;
			}
		}
		$properties = $this->properties;

		$flags = array_flip($flags);

		if (isset($flags['traits'])) {
			if (!isset($this->traits_properties)) {
				$this->traits_properties = array();
				foreach ($this->getTraits() as $trait) {
					$this->traits_properties = array_merge(
						$trait->getProperties(array('traits')), $this->traits_properties
					);
				}
			}
			$properties = array_merge($this->traits_properties, $properties);
		}

		if (isset($flags['inherited'])) {
			if (!isset($this->inherited_properties)) {
				$this->inherited_properties = ($parent = $this->getParent())
					? $parent->getProperties(array('inherited', 'traits'))
					: array();
			}
			$properties = array_merge($this->inherited_properties, $properties);
		}

		return $properties;
	}

	//------------------------------------------------------------------------------------- getTraits
	/**
	 * @return Php_Class[]
	 */
	public function getTraits()
	{
		if (!isset($this->traits)) {
			$this->traits = array();
			$expr = '%'
				. '\n\s*use\s*'
				. '(?:([\\\\\w]+)(?:\s*\,\s*)?)+'
				. '\s*[\;\{]'
				. '%';
			$source = substr($this->source, strpos($this->source, $this->prototype));
			preg_match_all($expr, $source, $match);
			foreach ($match[1] as $trait) {
				$this->traits[] = self::fromClassName($this->searchFullClassName($trait));
			}
		}
		return $this->traits;
	}

	//------------------------------------------------------------------------------ implementsMethod
	/**
	 * Returns true if this class or any of its direct traits implements the method.
	 * Direct traits are all the traits of the class, not the traits of the parent classes.
	 *
	 * @param $method_name    string
	 * @param $include_traits boolean if false, look in class only
	 * @return boolean
	 */
	public function implementsMethod($method_name, $include_traits = true)
	{
		$methods = $this->getMethods($include_traits ? array('traits') : array());
		return isset($methods[$method_name]) && !$methods[$method_name]->isAbstract();
	}

	//---------------------------------------------------------------------------- implementsProperty
	/**
	 * Returns true if this class or any of its direct traits declares/implements the property.
	 * Direct traits are all the traits of the class, not the traits of the parent classes.
	 *
	 * @param $property_name  string
	 * @param $include_traits boolean if false, look in class only
	 * @return boolean
	 */
	public function implementsProperty($property_name, $include_traits = true)
	{
		$properties = $this->getProperties($include_traits ? array('traits') : array());
		return isset($properties[$property_name]);
	}

	//------------------------------------------------------------------------------- parseImplements
	/**
	 * @param $buffer string
	 * @return string[]
	 */
	private static function parseImplements($buffer)
	{
		$expr = '%(?:([\\\\\w]+)(?:\s*\,\s*)?)%';
		preg_match_all($expr, $buffer, $match);
		return $match[1];
	}

	//----------------------------------------------------------------------------------------- regex
	/**
	 * @return string
	 */
	public static function regex()
	{
		return '%'
		. 'namespace\s+([\\\\\w]+)\s*?[\{\;]'       // 1 : namespace
		. '(?:.*\n)*?'                              // next lines
		. '(\n\s*?)'                                // 1 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 2 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '('                                       // 3 : prototype
		. '(?:(abstract)\s+)?'                      // 4 : abstract
		. '(class|interface|trait)\s+'              // 5 : type = class, interface or trait
		. '(\w+)\s*'                                // 6 : name
		. '(?:extends\s+([\\\\\w]+)\s*)?'           // 7 : extends
		. '(?:implements\s+((?:.*?\n)*?)\s*)?'      // 8 : implements
		. '\{'                                      // start class code
		. ')'
		. '%';
	}

	//--------------------------------------------------------------------------- searchFullClassName
	/**
	 * Search the full class name using imports
	 *
	 * @param $class_name string
	 * @return string
	 */
	private function searchFullClassName($class_name)
	{
		$check = '\\' . lLastParse($class_name, '\\');
		$count = strlen($check);
		foreach ($this->getImports() as $import) {
			if (substr("\\" . $import, -$count) === $check) {
				return substr($import, 0, -$count) . '\\' . $class_name;
			}
		}
		if (substr($class_name, 0, 1) == '\\') return substr($class_name, 1);
		if (!strpos($class_name, '\\'))        return $this->namespace . $check;
		return $class_name;
	}

}
