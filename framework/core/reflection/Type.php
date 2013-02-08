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
	 * @var string
	 */
	public $type;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $type_string
	 */
	public function __construct($type_string)
	{
		$this->type = $type_string;
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
		return $this->isSubClassOf("DateTime");
	}

	//--------------------------------------------------------------------------------------- isFloat
	/**
	 * @return boolean
	 */
	public function isFloat()
	{
		return $this->type == "float";
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

	//---------------------------------------------------------------------------------- isSubClassOf
	/**
	 * @param $class_name string
	 * @return boolean
	 */
	public function isSubClassOf($class_name)
	{
		return $this->isClass() && is_subclass_of($this->type, $class_name);
	}

	//-------------------------------------------------------------------------------------- multiple
	/**
	 * Returns the multiple type for given type
	 *
	 * @return Type
	 */
	public function multiple()
	{
		return new Type($this->type . "[]");
	}

	//------------------------------------------------------------------------------------- usesTrait
	/**
	 * Returns true if the class type uses the given trait
	 * @param $trait_name string
	 * @return boolean
	 */
	public function usesTrait($trait_name)
	{
		return $this->isClass() && in_array($trait_name, class_uses($this->getElementTypeAsString()));
	}

}
