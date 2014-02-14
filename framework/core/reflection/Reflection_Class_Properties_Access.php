<?php
namespace SAF\Framework;

/**
 * Internal properties access to enable listing and enablig read/write of all properties of a class,
 * including its private / protected properties.
 *
 * For use by Reflection_Class
 */
abstract class Reflection_Class_Properties_Access
{

	//---------------------------------------------------------------------------------------- $count
	/**
	 * Associates each class name to the number of time properties accessibility have been forced
	 * Each entry will be released when counter was zero before done() for the matching class name.
	 *
	 * @var integer[]
	 */
	private static $count = array();

	//------------------------------------------------------------------------------- $properties_map
	/**
	 * Associates each class name to an array associating property name and Reflection_Property
	 * This is used for caching and will be released once counter becomes zero for a given class name.
	 *
	 * @var Reflection_Property[][]
	 */
	private static $properties_map = array();

	//---------------------------------------------------------------------------------- $private_map
	/**
	 * Associates each class name to an array associating property name and Reflection_Property
	 * This is used for caching and will be released once counter becomes zero for a given class name.
	 *
	 * @var Reflection_Property[][]
	 */
	private static $private_map = array();

	//---------------------------------------------------------------------------------------- access
	/**
	 * Do not use this directly ! Please use Reflection_Class->accessProperties() instead.
	 *
	 * Change all properties accessibility to true for a given class, and return all class and parents properties list
	 *
	 * Done for class properties and its parents.
	 * Accessibility should be set back with a call to done() after use.
	 * If class properties are set to accessible several times, they will become non-accessible after the same number of done() calls.
	 *
	 * @param $class Reflection_Class
	 * @return Reflection_Property[]
	 * @see Reflection_Class::accessProperties()
	 */
	public static function access(Reflection_Class $class)
	{
		$class_name = $class->name;
		if (isset(self::$properties_map[$class_name])) {
			$properties = self::$properties_map[$class_name];
			self::$count[$class_name]++;
		}
		else {
			$parent_class = $class->getParentClass();
			// todo recursion is not needed : getProperties() gets all properties !
			// todo replace references to class_names with $property->class for correct handling
			$parent_properties = $parent_class
				? static::access($parent_class)
				: array();
			$properties = $parent_properties;
			$private = array();
			foreach ($class->getProperties() as $property) {
				$is_accessible = $property->isPublic();
				if (!$is_accessible) {
					$property->setAccessible(true);
					$private[] = $property;
				}
				$properties[$property->name] = $property;
			}
			self::$count[$class_name]          = 0;
			self::$properties_map[$class_name] = $properties;
			self::$private_map[$class_name]    = $private;
		}
		return $properties;
	}

	//------------------------------------------------------------------------------------ accessDone
	/**
	 * Do not use this directly ! Please use Reflection_Class->accessPropertiesDone() instead.
	 *
	 * All private class and parents properties go back to private
	 *
	 * This must be called after the properties used with access() are no longer needed as accessible.
	 * If more than one access() has been called for the class, the release will be done only on the last done() access.
	 *
	 * @param $class Reflection_Class
	 * @see Reflection_Class::accessPropertiesDone()
	 */
	public static function done(Reflection_Class $class)
	{
		$class_name = $class->name;
		$count = self::$count[$class_name];
		if ($count > 0) {
			self::$count[$class_name]--;
		}
		else {
			/** @var $private Reflection_Property */
			foreach (self::$private_map[$class_name] as $private) {
				$private->setAccessible(false);
			}
			unset(self::$private_map[$class_name]);
			unset(self::$properties_map[$class_name]);
			unset(self::$count[$class_name]);
			$parent_class = $class->getParentClass();
			if ($parent_class) {
				static::done($parent_class);
			}
		}
	}

}
