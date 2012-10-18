<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/classes/toolbox/Aop.php";
require_once "framework/classes/reflection/Reflection_Property.php";

abstract class Aop_Getter extends Aop
{

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@getter Aop::getCollection" annotation
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function getCollection(AopJoinpoint $joinpoint)
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
	 * @param AopJoinpoint $joinpoint
	 */
	public static function getDateTime(AopJoinpoint $joinpoint)
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
	 * @param AopJoinpoint $joinpoint
	 */
	public static function getObject(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$antiloop[$hash][$property] = true;
			$id_property = "id_" . $property;
			$value = $object->$property;
			if (!is_object($value)) {
				$class = $joinpoint->getClassName();
				$type = Namespaces::fullClassName(
					Reflection_Property::getInstanceOf($class, $property)->getType()
				);
				if (isset($value)) {
					$object->$property = Getter::getObject($value, $type);
				} else {
					$id_property = "id_" . $property;
					$object->$property = Getter::getObject($object->$id_property, $type);
				}
			} else {
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		aop_add_after(
			__NAMESPACE__ . "\\Autoloader->autoload()",
			array(__CLASS__, "registerGettersAop")
		);
	}

	//------------------------------------------------------------------------------- registerGetters
	/**
	 * Auto-register properties getters for a given class name
	 *
	 * Call this each time a class is declared (ie at end of Autoloader->autoload()) to automatically register AOP special getters for object properties.
	 * This uses the property @getter annotation to know what getter to use.
	 * Specific Aop::getMethod() getters are allowed shortcuts for SAF\Framework\Aop_Getter::getMethod().
	 *
	 * @param string $class_name
	 */
	public static function registerGetters($class_name)
	{
		parent::registerProperties($class_name, "getter", "read");
	}

	//---------------------------------------------------------------------------- registerGettersAop
	/**
	 * AOP auto-registerer call (to register after Autoloader->autoload(), crashes with AOP-PHP 0.2.0)
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function registerGettersAop(AopJoinpoint $joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			list($class_name) = $joinpoint->getArguments();
			parent::registerProperties(Namespaces::fullClassName($class_name), "getter", "read");
		}
	}

}
