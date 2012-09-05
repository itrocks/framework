<?php
namespace SAF\Framework;

class Aop_Getter
{

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for all objects collection fields using annotation @getter Aop::getCollection
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getCollection($joinpoint)
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
	 * Register this for all DateTime fields using annotation @getter Aop::getDateTime
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getDateTime($joinpoint)
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
	 * Register this for all object fields using annotation @getter Aop::getObject
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getObject($joinpoint)
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
	 * @param AopJoinPoint $joinpoint
	 */
	public static function _registerPropertiesGetters($joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			list($class_name) = $joinpoint->getArguments();
			Aop_Getter::registerPropertiesGetters($class_name);
		}
	}

	//---------------------------------------------------------------------- registerPropertiesGetter
	/**
	 * @param string $class_name
	 */
	public static function registerPropertiesGetters($class_name)
	{
		if (@class_exists($class_name)) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->getProperties() as $property) {
				$getter = $property->getGetterName();
				if ($getter) {
					if (substr($getter, 0, 5) === "Aop::") {
						Aop::registerBefore(
							"read " . $class_name . "->" . $property->name,
							array("SAF\\Framework\\Aop_Getter", substr($getter, 5))
						);
					} else {
						Aop::registerAround(
							"read " . $class_name . "->" . $property->name,
							array($class_name, $getter)
						);
					}
				}
			}
		}
	}

}

/*
// commented because AOP-PHP version 0.2.0 crashes when registering into an advice !
Aop::registerAfter(
	"SAF\\Framework\\Autoloader->autoload()",
	"SAF\\Framework\\Aop_Getter::_registerPropertiesGetters"
);
*/
