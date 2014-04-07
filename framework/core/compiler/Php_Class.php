<?php
namespace SAF\Framework;

use SAF\PHP\Reflection_Class;

/**
 * Object representation of a Php class read from source
 *
 * @deprecated Replaced by SAF\PHP\Reflection_Class
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

	//------------------------------------------------------------------------------- $documentations
	/**
	 * @var string
	 */
	private $documentations;

	//------------------------------------------------------------------------------------ $file_name
	/**
	 * @var string
	 */
	public $file_name;

	//---------------------------------------------------------------------------------------- $final
	/**
	 * @var string 'final' or null
	 */
	public $final;

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
		// remove all '\r'
		$buffer = trim(str_replace(CR, '', $buffer));
		// remove since the line containing '//#### AOP' until the end of the file
		$expr = '%\n\s*//\#+\s+AOP.*%s';
		preg_match($expr, $buffer, $match1);
		$buffer = preg_replace($expr, '$1', $buffer) . ($match1 ? LF . LF . '}' . LF : LF);
		// replace '/* public */ private [static] function name_?(' by 'public [static] function name('
		$expr = '%'
			. '(?:\n\s*/\*\*?\s+@noinspection\s+PhpUnusedPrivateMethodInspection.*?\*/)?'
			. '(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)' // 1 2 3
			. '(?:(?:private|protected|public)\s+)?'
			. '(static\s+)?' // 4
			. 'function\s*(\s?\&\s?)?\s*(\w+)\_[0-9]*\s*' // 5 6
			. '\('
			. '%';

		preg_match($expr, $buffer, $match2);
		$buffer = preg_replace($expr, '$1$2$3$4function $5$6(', $buffer);
		return $match1 || $match2;
	}

	//--------------------------------------------------------------------------------- fromClassName
	/**
	 * @param $class_name string
	 * @return Php_Class
	 */
	/*
	public static function fromClassName($class_name)
	{
		$reflection = new Reflection_Class($class_name);
		$filename = $reflection->getFileName();
		$class = $filename ? self::fromFile($filename) : self::fromReflection($reflection);
		if (!$class) {
			$class = self::fromReflection($reflection);
		}
		return $class;
	}
	*/

	//------------------------------------------------------------------------------------ fromSource
	/**
	 * @param $source Php_Source
	 */
	public static function fromSource(Php_Source $source)
	{

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
		if (ctype_upper(rLastParse($file_name, SL)[0])) {
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
			$class->final         = $match[5][$n];
			$class->abstract      = empty($match[6][$n]) ? null : $match[6][$n];
			$class->type          = $match[7][$n];
			$class->name          = ($class->namespace ? $class->namespace . BS : '') . $match[8][$n];
			$class->parent        = empty($match[9][$n]) ? null : $match[9][$n];
			$class->interfaces    = empty($match[10][$n]) ? [] : self::parseImplements($match[10][$n]);
		}
		else {
			$class->namespace     = $match[1];
			$class->indent        = $match[2];
			$class->documentation = $match[3];
			$class->prototype     = $match[4];
			$class->final         = $match[5];
			$class->abstract      = empty($match[6]) ? null : $match[6];
			$class->type          = $match[7];
			$class->name          = ($class->namespace ? $class->namespace . BS : '') . $match[8];
			$class->parent        = empty($match[9]) ? null : $match[9];
			$class->interfaces    = empty($match[10]) ? [] : self::parseImplements($match[10]);
		}
		class_exists($class->name);
		return $class;
	}

	//--------------------------------------------------------------------------------- fromPhpSource
	/**
	 * Build a Php_Class object using a Php_Source containing one class
	 *
	 * @param $source Php_Source
	 * @return Php_Class
	 */
	public static function fromPhpSource(Php_Source $source)
	{
		$classes = $source->getClasses();
		if (count($classes) != 1) {
			trigger_error(
				'PHP script source should contain one and only one class : ' . $source->file_name,
				E_USER_ERROR
			);
		}
		/** @var $source_class Dependency_Class */
		$source_class = reset($classes);
		$reflection = new Reflection_Class($source, $source_class->name);
		$class = new Php_Class();
		$class->abstract = $reflection->isAbstract() ? 'abstract' : null;
		$class->documentation = $reflection->getDocComment();
		$class->imports = [];
		$class->inherited_methods = [];
		$class->inherited_properties = [];
		$class->interfaces = [];
		foreach ($reflection->getInterfaces() as $interface) {
			$class->interfaces[$interface->name] = Php_Class::fromPhpSource(
				$interface
			);
		}
		$class->internal = true;
		$class->methods = [];
		foreach ($reflection->getMethods() as $method) {
			$class->methods[$method->name] = Php_Method::fromReflection($class, $method);
		}
		$class->name = $reflection->name;
		$class->namespace = lLastParse($reflection->name, BS, 1, false);
		$class->parent = false;
		$class->properties = [];
		foreach ($reflection->getProperties() as $property) {
			$class->properties[$property->name] = Php_Property::fromReflection($class, $property);
		}
		$class->traits = [];
		foreach ($reflection->getTraits() as $trait) {
			$class->traits[$trait->name] = Php_Class::fromReflection($trait);
		}
		$class->traits_methods = [];
		$class->traits_properties = [];
		$class->type = $reflection->isInterface() ? 'interface' : (
		$reflection->isTrait() ? 'trait' : 'class'
		);
		return $class;
	}

	//----------------------------------------------------------------------------- getDocumentations
	/**
	 * Gets cumulated documentations of parents and the class itself
	 *
	 * @param $filter array 'parents', 'interfaces' and 'traits'
	 * @return string
	 */
	public function getDocumentations($filter = ['parents', 'interfaces', 'traits'])
	{
		if (!isset($this->documentations)) {
			$this->documentations = $this->documentation;
			if (($this->type !== 'interface') && in_array('traits', $filter)) {
				foreach ($this->getTraits() as $trait) {
					$this->documentations .= $trait->getDocumentations($filter);
				}
			}
			if (($this->type === 'class') && in_array('parents', $filter)) {
				$parent = $this->getParent();
				if ($parent) {
					$this->documentations .= $parent->getDocumentations($filter);
				}
			}
			if (($this->type !== 'trait') && in_array('interfaces', $filter)) {
				foreach ($this->getInterfaces() as $interface) {
					$this->documentations .= $interface->getDocumentations($filter);
				}
			}
		}
		return $this->documentations;
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
			$this->imports = $match ? $match[1] : [];
		}
		return $this->imports;
	}

	//------------------------------------------------------------------------------------ getMethods
	/**
	 * @param $flags string[] 'inherited', 'traits'
	 * @return Php_Method[]
	 */
	public function getMethods($flags = [])
	{
		if (!isset($this->methods)) {
			$this->methods = [];
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
				$this->traits_methods = [];
				foreach ($this->getTraits() as $trait) {
					$this->traits_methods = array_merge(
						$trait->getMethods(['traits']), $this->traits_methods
					);
				}
			}
			$methods = array_merge($this->traits_methods, $methods);
		}

		if (isset($flags['inherited'])) {
			if (!isset($this->inherited_methods)) {
				$this->inherited_methods = [];
				foreach ($this->getInterfaces() as $interface) {
					$this->inherited_methods = array_merge(
						$interface->getMethods(['inherited', 'traits']), $this->inherited_methods
					);
				}
				if ($parent = $this->getParent()) {
					$this->inherited_methods = array_merge(
						$parent->getMethods(['inherited', 'traits']), $this->inherited_methods
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
	public function getProperties($flags = [])
	{
		if (!isset($this->properties)) {
			$this->properties = [];
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
				$this->traits_properties = [];
				foreach ($this->getTraits() as $trait) {
					$this->traits_properties = array_merge(
						$trait->getProperties(['traits']), $this->traits_properties
					);
				}
			}
			$properties = array_merge($this->traits_properties, $properties);
		}

		if (isset($flags['inherited'])) {
			if (!isset($this->inherited_properties)) {
				$this->inherited_properties = ($parent = $this->getParent())
					? $parent->getProperties(['inherited', 'traits'])
					: [];
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
			$this->traits = [];
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
		$methods = $this->getMethods($include_traits ? ['traits'] : []);
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
		$properties = $this->getProperties($include_traits ? ['traits'] : []);
		return isset($properties[$property_name]);
	}

	//------------------------------------------------------------------------------------ isAbstract
	/**
	 * @return boolean
	 */
	public function isAbstract()
	{
		return $this->abstract || ($this->type == 'interface') || ($this->type == 'trait');
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
		. '(\n\s*?)'                                // 2 : indent
		. '(?:(/\*\*\n(?:\s*\*.*\n)*\s*\*/)\n\s*)?' // 3 : documentation
		. '(?:\/\*.*\*/\n\s*)?'                     // ignored one-line documentation
		. '('                                       // 4 : prototype
		. '(?:(final)\s+)?'                         // 5 : final
		. '(?:(abstract)\s+)?'                      // 6 : abstract
		. '(class|interface|trait)\s+'              // 7 : type = class, interface or trait
		. '(\w+)\s*'                                // 8 : name
		. '(?:extends\s+([\\\\\w]+)\s*)?'           // 9 : extends
		. '(?:implements\s+((?:.*?\n)*?)\s*)?'      // 10 : implements
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
		$check = BS . lLastParse($class_name, BS);
		$count = strlen($check);
		foreach ($this->getImports() as $import) {
			if (substr(BS . $import, -$count) === $check) {
				return substr($import, 0, -$count) . BS . $class_name;
			}
		}
		if (substr($class_name, 0, 1) == BS) return substr($class_name, 1);
		if (!strpos($class_name, BS))        return $this->namespace . $check;
		return $class_name;
	}

}
