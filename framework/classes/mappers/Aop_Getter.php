<?php
namespace SAF\Framework;
use AopJoinPoint;

require_once "framework/classes/reflection/Reflection_Class.php";
require_once "framework/classes/reflection/Reflection_Property.php";

abstract class Aop_Getter
{

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@getter Aop::getCollection" annotation
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getCollection(AopJoinPoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$antiloop[$hash][$property] = true;
			$value = $object->$property;
			unset($antiloop[$hash][$property]);
			if (!is_array($value)) {
				$class = $joinpoint->getClassName();
				$type = Reflection_Property::getInstanceOf($class, $property)->getType();
				$type = Namespaces::defaultFullClassName(substr($type, strpos($type, ":") + 1), $class);
				$object->$property = Getter::getCollection($value, $type, $object);
			}
		}
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@getter Aop::getDateTime" annotation
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getDateTime(AopJoinPoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$antiloop[$hash][$property] = true;
			$value = $object->$property;
			unset($antiloop[$hash][$property]);
			if (is_string($value)) {
				$object->$property = Date_Time::fromISO($value);
			}
		}
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Register this for any object property using "@getter Aop::getObject" annotation
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getObject(AopJoinPoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$antiloop[$hash][$property] = true;
			$value = $object->$property;
			unset($antiloop[$hash][$property]);
			if (!is_object($value)) {
				$class = $joinpoint->getClassName();
				$type = Reflection_Property::getInstanceOf($class, $property)->getType();
				$object->$property = Getter::getObject($value, $type);
			}
		}
	}

	//--------------------------------------------------------------------- registerPropertiesGetters
	/**
	 * AOP auto-registerer call (to register after Autoloader->autoload(), crashes with AOP-PHP 0.2.0)
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function _registerPropertiesGetters(AopJoinPoint $joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			list($class_name) = $joinpoint->getArguments();
			Aop_Getter::registerPropertiesGetters($class_name);
		}
	}

	//---------------------------------------------------------------------- registerPropertiesGetter
	/**
	 * Auto-register properties getters for a given class name
	 *
	 * Call this each time a class is declared (ie at end of Autoloader->autoload()) to automatically register AOP special getters for object properties.
	 * This uses the property @getter annotation to know what getter to use.
	 * Specific Aop::getMethod() getters are allowed shortcuts for SAF\Framework\Aop_Getter::getMethod().
	 *
	 * @param string $class_name
	 */
	public static function registerPropertiesGetters($class_name)
	{
		if (@class_exists($class_name)) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->getProperties() as $property) {
				if ($property->class == $class_name) {
					$getter = $property->getAnnotation("getter")->value;
					if ($getter) {
						if (substr($getter, 0, 5) === "Aop::") {
							Aop::registerBefore(
								"read " . $class_name . "->" . $property->name,
								array(__CLASS__, substr($getter, 5))
							);
						}
						else {
							Aop::registerAround(
								"read " . $class_name . "->" . $property->name,
								array($class_name, $getter)
							);
						}
					}
				} else {
				}
			}
		}
	}

}

// TODO test this with further versions of AOP-PHP, where issue #34 will be complete
/*
// commented because AOP-PHP version 0.2.0 crashes when registering into an advice !
Aop::registerAfter(
	__NAMESPACE__ . "\\Autoloader->autoload()",
	__NAMESPACE__ . "\\Aop_Getter::_registerPropertiesGetters"
);
*/
