<?php
namespace SAF\Framework;

class Type
{

	//---------------------------------------------------------------------------------- $basic_types
	/**
	 * These are the basic non-object php types
	 *
	 * @var string[]
	 */
	private static $basic_types = array(
		"boolean", "integer", "float", "string",
		"array", "resource", "callable", "null", "NULL"
	);

	//---------------------------------------------------------------------------------- $can_be_null
	/**
	 * true if the type accepts null values
	 *
	 * @example for @var object|null type definition
	 *
	 * @var boolean
	 */
	private $can_be_null = false;

	//-------------------------------------------------------------------------------- $numeric_types
	/**
	 * These are the numeric types
	 *
	 * @var string[]
	 */
	private static $numeric_types = array("integer", "float");

	//---------------------------------------------------------------------------------- $sized_types
	/**
	 * These are the basic types having size
	 *
	 * @var string[]
	 */
	private static $sized_types = array("integer", "float", "string");

	//----------------------------------------------------------------------------------------- $type
	/**
	 * The type name itself (only one type, does not include "|null" or any secondary types)
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
			if (($i = strpos($type_string, "|")) !== false) {
				if (!isset($can_be_null)) {
					$this->can_be_null = strpos($type_string, "|null");
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
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->type;
	}

	//----------------------------------------------------------------------------- asReflectionClass
	/**
	 * Gets a single or multiple class type as its Reflection_Class
	 *
	 * @return Reflection_Class
	 */
	public function asReflectionClass()
	{
		return Reflection_Class::getInstanceOf($this->getElementTypeAsString());
	}

	//-------------------------------------------------------------------------------------- asString
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
	 * @return string
	 */
	public function getElementTypeAsString()
	{
		$i = strpos($this->type, "[");
		return ($i !== false)
			? substr($this->type, 0, $i)
			: $this->type;
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

	//--------------------------------------------------------------------------------------- isArray
	/**
	 * @return boolean
	 */
	public function isArray()
	{
		return $this->type == "array";
	}

	//--------------------------------------------------------------------------------------- isBasic
	/**
	 * Tells if a type is a basic type or not
	 *
	 * Basic types are boolean, integer, float, string, array, resource, callable, null, NULL
	 * DateTime and Date_Time are considered comme basic too ! Use isStrictlyBasic if you don't want them
	 * Not basic types are *,[] objects, class names
	 *
	 * @return boolean
	 */
	public function isBasic()
	{
		return $this->isStrictlyBasic() || $this->isDateTime();
	}

	//--------------------------------------------------------------------------------------- isClass
	/**
	 * Returns true if type is a class or multiple classes
	 *
	 * @return bool
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
		return $this->isInstanceOf("DateTime");
	}

	//--------------------------------------------------------------------------------------- isFloat
	/**
	 * @return boolean
	 */
	public function isFloat()
	{
		return $this->type == "float";
	}

	//---------------------------------------------------------------------------------- isInstanceOf
	/**
	 * Returns true if the class type is an instance of a class or interface
	 *
	 * This does not work with traits ! Use usesTrait instead.
	 *
	 * @param $class_name string
	 * @return bool
	 */
	public function isInstanceOf($class_name)
	{
		return $this->isClass() && class_instanceof($this->getElementTypeAsString(), $class_name);
	}

	//------------------------------------------------------------------------------------- isInteger
	/**
	 * @return boolean
	 */
	public function isInteger()
	{
		return $this->type == "integer";
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * Tells if a type is an array / multiple type or not
	 *
	 * If type is a generic array, then returns true.
	 * If type is a typed array ("what"),[] then returns the array element type (ie "what").
	 * If type is no one of those, then returns false.
	 *
	 * @return boolean|string "multiple" if is multiple (useful for display), else false
	 */
	public function isMultiple()
	{
		return ((substr($this->type, -1) === "]") || $this->isArray()) ? "multiple" : false;
	}

	//------------------------------------------------------------------------------ isMultipleString
	/**
	 * @return boolean
	 */
	public function isMultipleString()
	{
		return $this->type == "string[]";
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

	//------------------------------------------------------------------------------- isStrictlyBasic
	/**
	 * Tells if a type is strictly a basic type or not
	 *
	 * Basic types are boolean, integer, float, string, array, resource, callable, null, NULL
	 * Not basic types are *,[] objects, class names, including DateTime
	 *
	 * @return boolean
	 */
	public function isStrictlyBasic()
	{
		return in_array($this->type, self::$basic_types);
	}

	//-------------------------------------------------------------------------------------- isString
	/**
	 * @return boolean
	 */
	public function isString()
	{
		return $this->type == "string";
	}

	//---------------------------------------------------------------------------------- isSubclassOf
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
		return new Type($this->type . "[]", $can_be_null);
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
		return $this->isClass() && class_uses_trait($this->getElementTypeAsString(), $trait_name);
	}

}

//-------------------------------------------------------------------------------- class_instanceof
/**
 * Returns true if an object / class (of one of its parents) uses (or is) a class
 *
 * All parent classes and interfaces are scanned recursively
 * This works if $class_name is an interface name or class name, but not if it is a trait name
 *
 * @param $object     object|string object or class name or interface name
 * @param $class_name object|string An object or object name or interface name
 * @return boolean
 */
function class_instanceof($object, $class_name)
{
	if (is_object($object))     $object     = get_class($object);
	if (is_object($class_name)) $class_name = get_class($class_name);
	return ($object === $class_name) || is_subclass_of($object, $class_name);
}

//-------------------------------------------------------------------------------- class_uses_trait
/**
 * Returns true if an object / class (or one of its parents) uses (or is) a trait
 *
 * All parent classes and traits are scanned recursively
 * This works if $trait_name is a class name too, but not if it is an interface name
 *
 * @param $object     object|string object or class name
 * @param $trait_name object|string a trait name
 * @return boolean
 */
function class_uses_trait($object, $trait_name)
{
	if (is_object($object))     $object     = get_class($object);
	if (is_object($trait_name)) $trait_name = get_class($trait_name);
	if ($object == $trait_name) {
		return true;
	}
	$traits = class_uses($object);
	if (in_array($trait_name, $traits)) {
		return true;
	}
	$parent_class = get_parent_class($object);
	if (!empty($parent_class) && class_uses_trait($parent_class, $trait_name)) {
		return true;
	}
	foreach (class_uses($object) as $trait) {
		if (class_uses_trait($trait, $trait_name)) {
			return true;
		}
	}
	return false;
}
