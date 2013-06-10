<?php
namespace SAF\Framework;

/**
 * A library of feature to transform PHP elements names
 */
abstract class Names
{

	//------------------------------------------------------------------------------ classToDirectory
	/**
	 * Changes 'A\Namespace\Class_Name' into "class_name"
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDirectory($class_name)
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//-------------------------------------------------------------------------------- classToDisplay
	/**
	 * Changes 'A\Namespace\Class_Name' into "class name"
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDisplay($class_name)
	{
		return strtolower(str_replace("_", " ", Namespaces::shortClassName($class_name)));
	}

	//--------------------------------------------------------------------------------- classToMethod
	/**
	 * Changes 'A\Namespace\Class_Name' into "className"
	 *
	 * @param $class_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function classToMethod($class_name, $prefix = null)
	{
		$method_name = str_replace("_", "", Namespaces::shortClassName($class_name));
		return $prefix ? $prefix . $method_name : lcfirst($method_name);
	}

	//------------------------------------------------------------------------------- classToProperty
	/**
	 * Changes 'A\Namespace\Class_Name' into "class_name"
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToProperty($class_name)
	{
		return strtolower(Namespaces::shortClassName($class_name));
	}

	//------------------------------------------------------------------------------------ classToSet
	/**
	 * Changes 'A\Namespace\Class_Name' into 'A\Namespace\Class_Names'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToSet($class_name)
	{
		return Reflection_Class::getInstanceOf($class_name)->getAnnotation("set")->value;
	}

	//---------------------------------------------------------------------------- displayToDirectory
	/**
	 * Changes "a text" do a valid normalized directory name (without spaces nor special characters)
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToDirectory($display)
	{
		return strtolower(str_replace(" ", "_", $display));
	}

	//----------------------------------------------------------------------------- displayToProperty
	/**
	 * Changes "a text" into "a_text"
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToProperty($display)
	{
		return strtolower(str_replace(" ", "_", $display));
	}

	//--------------------------------------------------------------------------------- methodToClass
	/**
	 * Changes "aMethodName" into "A_Method_Name"
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToClass($method_name)
	{
		return ucfirst(preg_replace('/([a-z])([A-Z])/', '$1_$2', $method_name));
	}

	//------------------------------------------------------------------------------- methodToDisplay
	/**
	 * Changes "aMethodName" into "a method name"
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToDisplay($method_name)
	{
		return strtolower(preg_replace('/([a-z])([A-Z])/', '$1 $2', $method_name));
	}

	//------------------------------------------------------------------------------ methodToProperty
	/**
	 * Changes "aMethodName" into "a_method_name"
	 *
	 * @param $method_name string
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

	//--------------------------------------------------------------------------- propertyPathToField
	/**
	 * Changes "a.name.and.sub_name" into "a[name][and][sub_name]"
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyPathToField($property_name)
	{
		if ($i = strpos($property_name, ".")) {
			$property_name = substr($property_name, 0, $i)
				. "[" . str_replace(".", "][", substr($property_name, $i + 1)) . "]";
		}
		return $property_name;
	}

	//------------------------------------------------------------------------------- propertyToClass
	/**
	 * Changes "a_property_name" into "A_Property_Name"
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToClass($property_name)
	{
		return str_replace(' ', '_', ucwords(str_replace('_', ' ', $property_name)));
	}

	//----------------------------------------------------------------------------- propertyToDisplay
	/**
	 * Changes "a_property_name" into "a property name"
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToDisplay($property_name)
	{
		return str_replace('_', ' ', $property_name);
	}

	//------------------------------------------------------------------------------ propertyToMethod
	/**
	 * Changes "a_property_name" into "aPropertyName"
	 *
	 * @param $property_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function propertyToMethod($property_name, $prefix = null)
	{
		$method = "";
		$name = explode("_", $property_name);
		foreach ($name as $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? $prefix . $method : lcfirst($method);
	}

	//------------------------------------------------------------------------------------ setToClass
	/**
	 * Changes 'A\Namespace\Class_Names' into 'A\Namespace\Class_Name'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function setToClass($class_name)
	{
		if (substr($class_name, -2) !== "ss") {
			if     (substr($class_name, -3) === "ies")  return substr($class_name, 0, -3) . "y";
			elseif (substr($class_name, -2) === "en")   return substr($class_name, 0, -2) . "an";
			elseif (substr($class_name, -4) === "sses") return substr($class_name, 0, -2);
			elseif (substr($class_name, -1) === "s")    return substr($class_name, 0, -1);
			else                                        return $class_name;
		}
		else return $class_name;
	}

}
