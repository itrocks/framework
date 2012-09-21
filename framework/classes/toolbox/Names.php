<?php
namespace SAF\Framework;

abstract class Names
{

	//-------------------------------------------------------------------------------- classToDisplay
	/**
	 * @param string $class_name
	 * @return string
	 */
	public static function classToDisplay($class_name)
	{
		return strtolower(str_replace("_", " ", Namespaces::shortClassName($class_name)));
	}

	//--------------------------------------------------------------------------------- classToMethod
	/**
	 * @param string $class_name
	 * @param string $prefix
	 * @return string
	 */
	public static function classToMethod($class_name, $prefix = null)
	{
		$method_name = str_replace('_', '', Namespaces::shortClassName($class_name));
		return $prefix ? $prefix . $method_name : lcfirst($method_name);
	}

	//------------------------------------------------------------------------------- classToProperty
	/**
	 * @param string $class_name
	 * @return string
	 */
	public static function classToProperty($class_name)
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//------------------------------------------------------------------------------------ classToSet
	/**
	 * @param string $class_name
	 * @return string
	 */
	public static function classToSet($class_name)
	{
		$class_name = Namespaces::shortClassName($class_name);
		if (substr($class_name, -1) === "y")
			return substr($class_name, 0, -1) . "ies";
		elseif (substr($class_name, -2) === "an")
			return substr($class_name, 0, -2) . "en";
		else
			return $class_name . "s";
	}

	//--------------------------------------------------------------------------------- methodToClass
	/**
	 * @param string $method_name
	 * @return string
	 */
	public static function methodToClass($method_name)
	{
		return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1_$2', $method_name));
	}

	//------------------------------------------------------------------------------- methodToDisplay
	/**
	 * @param string $method_name
	 * @return string
	 */
	public static function methodToDisplay($method_name)
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $method_name));
	}

	//------------------------------------------------------------------------------ methodToProperty
	/**
	 * @param string $method_name
	 * @return string
	 */
	public static function methodToProperty($method_name)
	{
		$property_name = strtolower(preg_replace('/([a-z])([A-Z])/', '$1_$2', $method_name));
		if ((substr($property_name, 0, 4) == "get_") || (substr($property_name, 0, 4) == "set_")) {
			$property_name = substr($property_name, 4);
		}
		return $property_name;
	}

	//------------------------------------------------------------------------------- propertyToClass
	/**
	 * @param string $property_name
	 * @return string
	 */
	public static function propertyToClass($property_name)
	{
		return str_replace(' ', '_', ucwords(str_replace('_', ' ', $property_name)));
	}

	//----------------------------------------------------------------------------- propertyToDisplay
	/**
	 * @param string $property_name
	 * @return string
	 */
	public static function propertyToDisplay($property_name)
	{
		return str_replace('_', ' ', $property_name);
	}

	//------------------------------------------------------------------------------ propertyToMethod
	/**
	 * @param string $property_name
	 * @param string $prefix
	 * @return string
	 */
	public static function propertyToMethod($property_name, $prefix = null)
	{
		$method = "";
		$name = explode("_", $property_name);
		foreach ($name as $key => $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? $prefix . $method : lcfirst($method);
	}

	//------------------------------------------------------------------------------------ setToClass
	/**
	 * @param string $class_name
	 * @return string
	 */
	public static function setToClass($class_name)
	{
		$class_name = Namespaces::shortClassName($class_name);
		if (substr($class_name, -3) === "ies")
			return substr($class_name, 0, -3) . "y";
		elseif (substr($class_name, -2) === "en")
			return substr($class_name, 0, -2) . "an";
		elseif (substr($class_name, -1) === "s")
			return substr($class_name, 0, -1);
		else
			return $class_name;
	}

}
