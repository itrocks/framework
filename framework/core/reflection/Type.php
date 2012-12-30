<?php
namespace SAF\Framework;

abstract class Type
{

	//---------------------------------------------------------------------------------- $basic_types
	/**
	 * These are the basic non-object php types
	 *
	 * @var multitype:string
	 */
	private static $basic_types = array(
		"boolean", "integer", "float", "string",
		"array", "resource", "callable", "null", "NULL",
		"Date_Time"
	);

	//-------------------------------------------------------------------------------- $numeric_types
	/**
	 * These are the numeric types
	 *
	 * @var multitype:string
	 */
	private static $numeric_types = array("integer", "float");

	//---------------------------------------------------------------------------------- $sized_types
	/**
	 * These are the basic types having size
	 *
	 * @var multitype:string
	 */
	private static $sized_types = array("integer", "float", "string");

	//--------------------------------------------------------------------------------------- hasSize
	/**
	 * Tells if a type has a siez or not
	 *
	 * @param string $type_name
	 * @return boolean
	 */
	public static function hasSize($type_name)
	{
		return in_array($type_name, self::$sized_types);
	}

	//--------------------------------------------------------------------------------------- isBasic
	/**
	 * Tells if a type is a basic type or not
	 *
	 * Basic types are boolean, integer, float, string, array, resource, callable, null, NULL
	 * Not basic types are multitype:*, objects, class names
	 *
	 * @param string $type_name
	 * @return boolean
	 */
	public static function isBasic($type_name)
	{
		return in_array(Namespaces::shortClassName($type_name), self::$basic_types);
	}

	//------------------------------------------------------------------------------------ isMultiple
	/**
	 * Tells if a type is an array / multitype or not
	 *
	 * If type is a generic array, then returns true.
	 * If type is a typed array ("multitype:what"), then returns the array element type (ie "what").
	 * If type is no one of those, then returns false.
	 *
	 * @param string $type_name
	 * @return boolean | string
	 */
	public static function isMultiple($type_name)
	{
		return (substr($type_name, 0, 10) === "multitype:")
			? substr($type_name, 10)
			: (($type_name == "array") ? true : false);
	}

	//------------------------------------------------------------------------------------- isNumeric
	/**
	 * Tells if a type is numeric or not
	 * 
	 * @param string $type_name
	 * @return boolean
	 */
	public static function isNumeric($type_name)
	{
		return in_array($type_name, self::$numeric_types);
	}

}
