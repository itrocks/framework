<?php
namespace Framework;

class Type
{

	private static $basic_types = array(
		"boolean", "integer", "float", "string",
		"array", "resource", "callable", "NULL"
	);
	//--------------------------------------------------------------------------------------- isBasic
	/**
	 * Tell if given type is a basic type or not
	 *
	 * @param  string $type_name
	 * @return boolean
	 */
	public static function isBasic($type_name)
	{
		return in_array($type_name, Type::$basic_types);
	}

}
