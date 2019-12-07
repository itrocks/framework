<?php
namespace ITRocks\Framework\Reflection;

use DateTime;
use ITRocks\Framework\Builder;
use ITRocks\Framework\PHP;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Tools\Stringable;

/**
 * PHP types manager
 */
class Type
{

	//---------------------------------------------------------------------------------------- _ARRAY
	const _ARRAY = 'array';

	//------------------------------------------------------------------------------------- _CALLABLE
	const _CALLABLE = 'callable';

	//--------------------------------------------------------------------------------------- BOOLEAN
	const BOOLEAN = 'boolean';

	//------------------------------------------------------------------------------------- DATE_TIME
	const DATE_TIME = 'date_time';

	//----------------------------------------------------------------------------------------- FLOAT
	const FLOAT = 'float';

	//--------------------------------------------------------------------------------------- INTEGER
	const INTEGER = 'integer';

	//----------------------------------------------------------------------------------------- MIXED
	const MIXED = 'mixed';

	//-------------------------------------------------------------------------------------- MULTIPLE
	const MULTIPLE = 'multiple';

	//------------------------------------------------------------------------------------------ NULL
	const NULL = 'NULL';

	//---------------------------------------------------------------------------------------- OBJECT
	const OBJECT = 'object';

	//-------------------------------------------------------------------------------------- RESOURCE
	const RESOURCE = 'resource';

	//---------------------------------------------------------------------------------------- STRING
	const STRING = 'string';

	//---------------------------------------------------------------------------------- STRING_ARRAY
	const STRING_ARRAY = 'string[]';

	//------------------------------------------------------------------------------------------ null
	const null = 'null';

	//------------------------------------------------------------------------------------- $absolute
	/**
	 * If true, the class name was given as an absolute (type string was beginning with a \)
	 *
	 * @var boolean
	 */
	private $absolute;

	//---------------------------------------------------------------------------------- $can_be_null
	/**
	 * true if the type accepts null values
	 *
	 * @example for @var object|null type definition
	 * @var boolean
	 */
	private $can_be_null = false;

	//-------------------------------------------------------------------------------- $numeric_types
	/**
	 * These are the numeric types
	 *
	 * @var string[]
	 */
	private static $numeric_types = [self::FLOAT, self::INTEGER];

	//---------------------------------------------------------------------------------- $sized_types
	/**
	 * These are the basic types having size
	 *
	 * @var string[]
	 */
	private static $sized_types = [self::FLOAT, self::INTEGER, self::STRING];

	//------------------------------------------------------------------------- $strictly_basic_types
	/**
	 * These are the basic non-object php types
	 *
	 * @var string[]
	 */
	private static $strictly_basic_types = [
		self::_ARRAY, self::BOOLEAN, self::_CALLABLE, self::FLOAT, self::INTEGER,
		self::NULL, self::null, self::RESOURCE, self::STRING
	];

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type name itself :
	 * - only one type, does not include '|null' or any secondary types
	 * - if this is a class name path, this will be full 'Namespace\Class' and never begin with '\'
	 *
	 * @var string
	 */
	private $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type_string string
	 * @param $can_be_null boolean
	 */
	public function __construct($type_string = null, $can_be_null = null)
	{
		if (isset($type_string)) {
			if (($i = strpos($type_string, '|')) !== false) {
				if (!isset($can_be_null)) {
					$this->can_be_null = strpos($type_string, '|' . self::null);
				}
				$this->type = substr($type_string, 0, $i);
			}
			else {
				$this->type = $type_string;
			}
		}
		if (isset($can_be_null)) {
			$this->can_be_null = $can_be_null;
		}
		if (!empty($this->type) && ($this->type[0] === BS)) {
			$this->absolute = true;
			$this->type     = substr($this->type, 1);
		}
		elseif (in_array($this->type, ['self', 'static'])) {
			$this->absolute = true;
		}
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type;
	}

	//-------------------------------------------------------------------------------- applyNamespace
	/**
	 * Apply namespace and use entries to the type name (if class)
	 *
	 * Return the full element class name, used to modify the type (multiple stays multiple)
	 *
	 * @param $namespace string
	 * @param $use       string[]
	 * @return string
	 */
	public function applyNamespace($namespace, array $use = [])
	{
		if (!$this->absolute && $this->isClass()) {
			$class_name = $this->getElementTypeAsString(false);
			$search     = BS . lParse($class_name, BS);
			$length     = strlen($search);
			foreach ($use as $u) {
				if (substr(BS . $u, -$length) === $search) {
					$found      = true;
					$class_name = $u . (strpos($class_name, BS) ? (BS . substr($class_name, $length)) : '');
					break;
				}
			}
			if (!isset($found)) {
				$class_name = ($namespace ? ($namespace . BS) : '') . $class_name;
			}
			$this->type     = $class_name . ($this->isMultiple() ? '[]' : '');
			$this->absolute = true;
			return $class_name;
		}
		return $this->type;
	}

	//----------------------------------------------------------------------------- asReflectionClass
	/**
	 * Gets a single or multiple class type as its Reflection_Class
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $reflection_class_name string Any reflection class name that implements Reflection_Class
	 * @return Interfaces\Reflection_Class|PHP\Reflection_Class|Reflection_Class
	 */
	public function asReflectionClass($reflection_class_name = null)
	{
		if ($reflection_class_name) {
			/** @noinspection PhpUnhandledExceptionInspection reflection class name must be valid */
			$reflection_class = is_a($reflection_class_name, PHP\Reflection_Class::class, true)
				? PHP\Reflection_Class::of($this->getElementTypeAsString())
				: (new Reflection_Class($reflection_class_name))->newInstance(
					$this->getElementTypeAsString()
				);
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection property var value must be valid */
			$reflection_class = new Reflection_Class($this->getElementTypeAsString());
		}
		return $reflection_class;
	}

	//-------------------------------------------------------------------------------------- asString
	/**
	 * Returns the type name as string
	 * - basic types
	 * - object types with their namespace, but never beginning with a '\'
	 *
	 * @example 'string', 'ITRocks\Framework\Tools\Date_Time'
	 * @return string
	 */
	public function asString()
	{
		return $this->type;
	}

	//------------------------------------------------------------------------------------- canBeNull
	/**
	 * Returns true if the type accepts null values
	 *
	 * @return boolean
	 */
	public function canBeNull()
	{
		return $this->can_be_null;
	}

	//------------------------------------------------------------------------------- getDefaultValue
	/**
	 * Gets default value for the type
	 *
	 * Depends on if it can be null or not, and depends on simple type
	 *
	 * @return mixed
	 */
	public function getDefaultValue()
	{
		if ($this->canBeNull()) {
			return null;
		}
		if ($this->isMultiple()) {
			return [];
		}
		else switch ($this->asString()) {
			case self::BOOLEAN: return false;
			case self::INTEGER: return 0;
			case self::FLOAT:   return 0.0;
			case self::STRING:  return '';
		}
		return null;
	}

	//-------------------------------------------------------------------------------- getElementType
	/**
	 * Gets a multiple type single element type
	 *
	 * @return Type
	 */
	public function getElementType()
	{
		return ($this->isMultiple())
			? new Type($this->getElementTypeAsString())
			: $this;
	}

	//------------------------------------------------------------------------ getElementTypeAsString
	/**
	 * Gets a multiple type single element class name
	 *
	 * @param $build boolean false if you need to keep the original name of the class, without Build
	 * @return string
	 */
	public function getElementTypeAsString($build = true)
	{
		$i = strpos($this->type, '[');
		// TODO NORMAL Builder : look where it is really useful, and remove it from all other places
		$string = ($i !== false) ? substr($this->type, 0, $i) : $this->type;
		return $build ? Builder::className($string) : $string;
	}

	//--------------------------------------------------------------------------------------- hasSize
	/**
	 * Tells if a type has a size or not
	 *
	 * @return boolean
	 */
	public function hasSize()
	{
		return in_array($this->type, self::$sized_types);
	}

	//------------------------------------------------------------------------------- isAbstractClass
	/**
	 * Returns true if the class is abstract (works with class types only)
	 * object is considered as abstract
	 *
	 * @return boolean
	 */
	public function isAbstractClass()
	{
		return beginsWith($this->type, static::MIXED)
			|| beginsWith($this->type, static::OBJECT)
			|| $this->asReflectionClass()->isAbstract();
	}

	//--------------------------------------------------------------------------------------- isArray
	/**
	 * @return boolean
	 */
	public function isArray()
	{
		return $this->type === self::_ARRAY;
	}

	//--------------------------------------------------------------------------------------- isBasic
	/**
	 * Tells if a type is a basic type or not
	 *
	 * Basic types : boolean, integer, float, string, string[], array, resource, callable, null, NULL
	 * DateTime and Date_Time are considered as basic too ! Use isStrictlyBasic
	 * if you don't want them
	 * Not basic types are *, [] objects, class names
	 *
	 * @param $include_multiple_string boolean if false, string[] is not considered as a basic type
	 * @return boolean
	 */
	public function isBasic($include_multiple_string = true)
	{
		return $this->isStrictlyBasic()
			|| $this->isDateTime()
			|| ($include_multiple_string && $this->isMultipleString());
	}

	//------------------------------------------------------------------------------------- isBoolean
	/**
	 * Returns true if type is a boolean
	 *
	 * @return boolean
	 */
	public function isBoolean()
	{
		return $this->type === self::BOOLEAN;
	}

	//--------------------------------------------------------------------------------------- isClass
	/**
	 * Returns true if type is a class or multiple classes
	 *
	 * @return boolean
	 */
	public function isClass()
	{
		return !$this->getElementType()->isStrictlyBasic();
	}

	//------------------------------------------------------------------------------------ isDateTime
	/**
	 * @return boolean
	 */
	public function isDateTime()
	{
		return $this->isInstanceOf(DateTime::class);
	}

	//--------------------------------------------------------------------------------------- isFloat
	/**
	 * @return boolean
	 */
	public function isFloat()
	{
		return $this->type === self::FLOAT;
	}

	//---------------------------------------------------------------------------------- isInstanceOf
	/**
	 * Returns true if the class type is an instance of a class or interface
	 *
	 * This does not work with traits ! Use usesTrait instead.
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function isInstanceOf($class_name)
	{
		if ($this->isClass()) {
			if ($class_name === static::OBJECT) {
				return true;
			}
			$element_type_string = $this->getElementTypeAsString();
			return ($element_type_string !== 'object') && is_a($element_type_string, $class_name, true);
		}
		return false;
	}

	//------------------------------------------------------------------------------------- isInteger
	/**
	 * @return boolean
	 */
	public function isInteger()
	{
		return $this->type === self::INTEGER;
	}

	//--------------------------------------------------------------------------------------- isMixed
	/**
	 * @return boolean
	 */
	public function isMixed()
	{
		return $this->type === self::MIXED;
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * Tells if a type is an array / multiple type or not
	 *
	 * If type is a generic array, then returns true.
	 * If type is a typed array ('what'),[] then returns the array element type (ie 'what').
	 * If type is no one of those, then returns false.
	 *
	 * @return boolean|string 'multiple' if is multiple (useful for display), else false
	 */
	public function isMultiple()
	{
		return ((substr($this->type, -2) === '[]') || $this->isArray()) ? self::MULTIPLE : false;
	}

	//------------------------------------------------------------------------------- isMultipleClass
	/**
	 * Returns true if type is a multiple class
	 *
	 * @return boolean
	 */
	public function isMultipleClass()
	{
		return $this->isMultiple() && $this->isClass();
	}

	//------------------------------------------------------------------------------ isMultipleString
	/**
	 * @return boolean
	 */
	public function isMultipleString()
	{
		return $this->type === self::STRING_ARRAY;
	}

	//---------------------------------------------------------------------------------------- isNull
	/**
	 * @return boolean
	 */
	public function isNull()
	{
		return in_array($this->type, [self::NULL, self::null]);
	}

	//------------------------------------------------------------------------------------- isNumeric
	/**
	 * Tells if a type is numeric or not
	 *
	 * @return boolean
	 */
	public function isNumeric()
	{
		return in_array($this->type, self::$numeric_types);
	}

	//-------------------------------------------------------------------------------------- isObject
	/**
	 * @return boolean
	 */
	public function isObject()
	{
		return ($this->type === static::OBJECT);
	}

	//--------------------------------------------------------------------------------- isSingleClass
	/**
	 * Returns true if type is a single class
	 *
	 * @return boolean
	 */
	public function isSingleClass()
	{
		return !($this->isMultiple()) && $this->isClass();
	}

	//------------------------------------------------------------------------------- isStrictlyBasic
	/**
	 * Tells if a type is strictly a basic type or not
	 *
	 * Strictly basic types are boolean, integer, float, string, array, resource, callable, null, NULL
	 * Not basic types are *, [] objects, class names, including DateTime and string[]
	 *
	 * @return boolean
	 */
	public function isStrictlyBasic()
	{
		return in_array($this->type, self::$strictly_basic_types);
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * @return boolean
	 */
	public function isString()
	{
		return $this->type === self::STRING;
	}

	//---------------------------------------------------------------------------------- isStringable
	/**
	 * @return boolean
	 */
	public function isStringable()
	{
		return (
			$this->isClass()
			&& !$this->isMultiple()
			&& is_a($this->getElementTypeAsString(), Stringable::class, true)
		);
	}

	//---------------------------------------------------------------------------------- isSubClassOf
	/**
	 * Returns true if the class type is a subclass of a class or interface
	 *
	 * This does not work with traits ! Use usesTrait instead.
	 *
	 * @param $class_name string
	 * @return boolean
	 */
	public function isSubClassOf($class_name)
	{
		return $this->isClass() && is_subclass_of($this->getElementTypeAsString(), $class_name);
	}

	//-------------------------------------------------------------------------------------- multiple
	/**
	 * Returns the multiple type for given type
	 *
	 * @param $can_be_null boolean
	 * @return Type
	 */
	public function multiple($can_be_null = false)
	{
		return new Type($this->type . '[]', $can_be_null);
	}

	//------------------------------------------------------------------------------------- usesTrait
	/**
	 * Returns true if the class type uses the given trait
	 *
	 * This goes into parents traits
	 *
	 * @param $trait_name string
	 * @return boolean
	 */
	public function usesTrait($trait_name)
	{
		return $this->isClass() && isA($this->getElementTypeAsString(), $trait_name);
	}

}
