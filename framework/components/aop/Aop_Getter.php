<?php
namespace SAF\Framework;
use AopJoinpoint;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/toolbox/Aop.php";
/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/Reflection_Property.php";

/**
 * Aop calls getter
 */
abstract class Aop_Getter extends Aop implements Plugin
{

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * If true, Aop_Getter getters are ignored to avoid side effects
	 * Don't forget to bring it back to false when you're done !
	 *
	 * @var boolean
	 */
	public static $ignore = false;

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@getter Aop::getCollection" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getCollection(AopJoinpoint $joinpoint)
	{
		if (!Aop_Getter::$ignore) {
			$object   = $joinpoint->getObject();
			$property = $joinpoint->getPropertyName();
			$hash     = spl_object_hash($object);
			static $antiloop = array();
			if (!isset($antiloop[$hash][$property])) {
				$antiloop[$hash][$property] = true;
				$value = isset($object->$property) ? $object->$property : null;
				unset($antiloop[$hash][$property]);
				if (!is_array($value)) {
					$class = $joinpoint->getClassName();
					$type_name = Reflection_Property::getInstanceOf($class, $property)->getType()
						->getElementTypeAsString();
					$object->$property = Getter::getCollection($value, $type_name, $object);
				}
			}
		}
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@getter Aop::getDateTime" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getDateTime(AopJoinpoint $joinpoint)
	{
		if (!Aop_Getter::$ignore) {
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
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Register this for any object property using "@getter Aop::getObject" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getObject(AopJoinpoint $joinpoint)
	{
		if (!Aop_Getter::$ignore) {
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
					$type = Reflection_Property::getInstanceOf($class, $property)->getType()->asString();
					$value = Getter::getObject($value, $type, $object, $property);
					if (!is_object($value)) {
						$value = Object_Builder::current()->newInstance($type);
					}
					$object->$property = $value;
				}
			}
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			__NAMESPACE__ . "\\Autoloader->classLoadEvent()",
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
	 * @param $class_name string
	 */
	public static function registerGetters($class_name)
	{
		parent::registerProperties($class_name, "getter", "read");
	}

	//---------------------------------------------------------------------------- registerGettersAop
	/**
	 * AOP auto-registerer call (to register after Autoloader->autoload(), crashes with AOP-PHP 0.2.0)
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function registerGettersAop(AopJoinpoint $joinpoint)
	{
		$class_name = $joinpoint->getArguments()[0];
		parent::registerProperties(Namespaces::fullClassName($class_name), "getter", "read");
	}

}
