<?php
namespace SAF\Framework\Tools;

use SAF\Framework\Application;
use SAF\Framework\Dao;
use SAF\Framework\Reflection\Reflection_Class;

/**
 * A library of feature to transform PHP elements names
 */
abstract class Names
{

	//------------------------------------------------------------------------------ classToDirectory
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class_name'
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
	 * Changes 'A\Namespace\Class_Name' into 'class name'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToDisplay($class_name)
	{
		return strtolower(str_replace('_', SP, Namespaces::shortClassName($class_name)));
	}

	//--------------------------------------------------------------------------------- classToMethod
	/**
	 * Changes 'A\Namespace\Class_Name' into 'className'
	 *
	 * @param $class_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function classToMethod($class_name, $prefix = null)
	{
		$method_name = str_replace('_', '', Namespaces::shortClassName($class_name));
		return $prefix ? $prefix . $method_name : lcfirst($method_name);
	}

	//----------------------------------------------------------------------------------- classToPath
	/**
	 * Changes 'A\Class\Name\Like\This' into 'a/class/name/like/This'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function classToPath($class_name)
	{
		$i = strrpos($class_name, BS);
		return str_replace(BS, SL, strtolower(substr($class_name, 0, $i)) . substr($class_name, $i));
	}

	//------------------------------------------------------------------------------- classToProperty
	/**
	 * Changes 'A\Namespace\Class_Name' into 'class_name'
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
		return (new Reflection_Class($class_name))->getAnnotation('set')->value;
	}

	//------------------------------------------------------------------------------------ classToUri
	/**
	 * Gets the URI of a class name or object
	 *
	 * @example class name User : 'SAF/Framework/User'
	 * @example User object of id = 1 : 'SAF/Framework/User/1'
	 * @param $class_name object|string
	 * @return string
	 */
	public static function classToUri($class_name)
	{
		// get object id, if object
		if (is_object($class_name)) {
			$id = Dao::getObjectIdentifier($class_name);
			$class_name = get_class($class_name);
		}
		// link classes : get linked class
		while ((new Reflection_Class($class_name))->getAnnotation('link')->value) {
			$class_name = get_parent_class($class_name);
		}
		// built classes : get object class
		$built_path = Application::current()->getNamespace() . BS . 'Built' . BS;
		while (substr($class_name, 0, strlen($built_path)) == $built_path) {
			$class_name = get_parent_class($class_name);
		}
		// replace \ by /
		return str_replace(BS, SL, $class_name) . (isset($id) ? (SL . $id) : '');
	}

	//-------------------------------------------------------------------------------- displayToClass
	/**
	 * Changes 'a text' do a valid normalized directory name (without spaces nor special characters)
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToClass($display)
	{
		return str_replace(SP, '_', ucwords(str_replace('_', SP, $display)));
	}

	//---------------------------------------------------------------------------- displayToDirectory
	/**
	 * Changes 'a text' do a valid normalized directory name (without spaces nor special characters)
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToDirectory($display)
	{
		return strtolower(str_replace(SP, '_', $display));
	}

	//----------------------------------------------------------------------------- displayToProperty
	/**
	 * Changes 'a text' into 'a_text'
	 *
	 * @param $display string
	 * @return string
	 */
	public static function displayToProperty($display)
	{
		return strtolower(str_replace(SP, '_', $display));
	}

	//--------------------------------------------------------------------------------- fileToDisplay
	/**
	 * Changes a 'full/path/file_name.ext' into 'file name'
	 *
	 * @param $file_name
	 * @return string
	 */
	public static function fileToDisplay($file_name)
	{
		if (($i = strpos($file_name, SL)) !== false) {
			$file_name = substr($file_name, $i + 1);
		}
		if (($i = strpos($file_name, DOT)) !== false) {
			$file_name = substr($file_name, 0, $i);
		}
		return str_replace('_', SP, $file_name);
	}

	//--------------------------------------------------------------------------------- methodToClass
	/**
	 * Changes 'aMethodName' into 'A_Method_Name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToClass($method_name)
	{
		return ucfirst(preg_replace('%([a-z])([A-Z])%', '$1_$2', $method_name));
	}

	//------------------------------------------------------------------------------- methodToDisplay
	/**
	 * Changes 'aMethodName' into 'a method name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToDisplay($method_name)
	{
		return strtolower(preg_replace('%([a-z])([A-Z])%', '$1 $2', $method_name));
	}

	//------------------------------------------------------------------------------ methodToProperty
	/**
	 * Changes 'aMethodName' into 'a_method_name'
	 *
	 * @param $method_name string
	 * @return string
	 */
	public static function methodToProperty($method_name)
	{
		$property_name = strtolower(preg_replace('%([a-z])([A-Z])%', '$1_$2', $method_name));
		if ((substr($property_name, 0, 4) == 'get_') || (substr($property_name, 0, 4) == 'set_')) {
			$property_name = substr($property_name, 4);
		}
		return $property_name;
	}

	//----------------------------------------------------------------------------------- pathToClass
	/**
	 * Changes 'a/class/name/like/This' into 'A\Class\Name\Like\This'
	 *
	 * @param $class_name string
	 * @return string
	 */
	public static function pathToClass($class_name)
	{
		return str_replace(SL, BS, ucfirst(preg_replace_callback(
			'%[_/][a-z]%', function($matches) { return strtoupper($matches[0]); }, $class_name
		)));
	}

	//--------------------------------------------------------------------------- propertyPathToField
	/**
	 * Changes 'a.name.and.sub_name' into 'a[name][and][sub_name]'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyPathToField($property_name)
	{
		if ($i = strpos($property_name, DOT)) {
			$property_name = substr($property_name, 0, $i)
				. '[' . str_replace(DOT, '][', substr($property_name, $i + 1)) . ']';
		}
		return $property_name;
	}

	//------------------------------------------------------------------------------- propertyToClass
	/**
	 * Changes 'a_property_name' into 'A_Property_Name'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToClass($property_name)
	{
		return str_replace(SP, '_', ucwords(str_replace('_', SP, $property_name)));
	}

	//----------------------------------------------------------------------------- propertyToDisplay
	/**
	 * Changes 'a_property_name' into 'a property name'
	 *
	 * @param $property_name string
	 * @return string
	 */
	public static function propertyToDisplay($property_name)
	{
		return str_replace('_', SP, $property_name);
	}

	//------------------------------------------------------------------------------ propertyToMethod
	/**
	 * Changes 'a_property_name' into 'aPropertyName'
	 *
	 * @param $property_name string
	 * @param $prefix string
	 * @return string
	 */
	public static function propertyToMethod($property_name, $prefix = null)
	{
		$method = '';
		$name = explode('_', $property_name);
		foreach ($name as $value) {
			$method .= ucfirst($value);
		}
		return $prefix ? $prefix . $method : lcfirst($method);
	}

	//------------------------------------------------------------------------------------ setToClass
	/**
	 * Changes 'A\Namespace\Class_Names' into 'A\Namespace\Class_Name'
	 *
	 * @param $class_name  string
	 * @param $check_class boolean false if you don't want to check for existing classes
	 * @return string
	 */
	public static function setToClass($class_name, $check_class = true)
	{
		$set_class_name = $class_name;
		$class_name = Namespaces::shortClassName($class_name);
		$right = '';
		do {
			if (substr($class_name, -2) !== 'ss') {
				if     (substr($class_name, -3) === 'ies')  $class_name = substr($class_name, 0, -3) . 'y';
				elseif (substr($class_name, -3) === 'ses')  $class_name = substr($class_name, 0, -2);
				elseif (substr($class_name, -4) === 'ches') $class_name = substr($class_name, 0, -2);
				elseif (substr($class_name, -1) === 's')    $class_name = substr($class_name, 0, -1);
				elseif (substr($class_name, -2) === 'en')   $class_name = substr($class_name, 0, -2) . 'an';
			}
			$full_class_name = Namespaces::defaultFullClassName($class_name . $right, $set_class_name);
			if (@class_exists($full_class_name)) {
				return $full_class_name;
			}
			$i = strrpos($class_name, '_');
			if (strrpos($class_name, BS) > $i) {
				$i = false;
			}
			if ($i === false) {
				if (
					@class_exists($set_class_name)
					&& ((new Reflection_Class($set_class_name))->getAnnotation('set')->value == $set_class_name)
				) {
					return $set_class_name;
				}
				elseif ($check_class && error_reporting()) {
					trigger_error('No class found for set ' . $set_class_name, E_USER_ERROR);
				}
				else {
					$right = substr($class_name, $i) . $right;
					$class_name = substr($class_name, 0, $i);
				}
			}
			else {
				$right = substr($class_name, $i) . $right;
				$class_name = substr($class_name, 0, $i);
			}
		}
		while (!empty($class_name));
		$class_name .= $right;
		return class_exists($class_name, false) ? $class_name : $set_class_name;
	}

}
