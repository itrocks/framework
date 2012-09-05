<?php
namespace SAF\Framework;

class Names
{

	//--------------------------------------------------------------------------------- methodToClass
	public static function methodToClass($method_name)
	{
		return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1_$2', $method_name));
	}

	//--------------------------------------------------------------------------------- classToMethod
	public static function classToMethod($class_name, $prefix = null)
	{
		$method_name = str_replace('_', '', Namespaces::shortClassName($class_name));
		return $prefix ? $prefix . $method_name : lcfirst($method_name);
	}

	//----------------------------------------------------------------------------- propertyToDisplay
	public static function propertyToDisplay($property_name)
	{
		return str_replace('_', ' ', $property_name);
	}

	//------------------------------------------------------------------------------ propertyToMethod
	public static function propertyToMethod($property_name, $prefix = null)
	{
		$method = "";
		$name = explode("_", $property_name);
		foreach ($name as $key => $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? $prefix . $method : lcfirst($method);
	}

}
