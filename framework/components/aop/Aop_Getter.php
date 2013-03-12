<?php
namespace SAF\Framework;
use AopJoinpoint;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Aop.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Property.php";

/**
 * Aop calls getter
 */
abstract class Aop_Getter extends Aop implements Plugin
{

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Register this using "@getter Aop::getAll" for a property that points on all existing elements of a collection
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function getAll(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$type_name = Reflection_Property::getInstanceOf($class, $property)->getType()
				->getElementTypeAsString();
			$object->$property = Getter::getAll(null, $type_name);
		}
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@link Collection" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getCollection(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$type_name = Reflection_Property::getInstanceOf($class, $property)->getType()
				->getElementTypeAsString();
			$object->$property = Getter::getCollection(null, $type_name, $object, $property);
		}
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@link DateTime" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getDateTime(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$value = $object->$property;
		if (is_string($value)) {
			$object->$property = Date_Time::fromISO($value);
		}
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Register this for any object map property using "@link Map" annotation
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function getMap(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$property = Reflection_Property::getInstanceOf($class, $property);
			$object->$property = Getter::getMap(null, $property, $object);
		}
	}

	//------------------------------------------------------------------------------------- getObject
	/**
	 * Register this for any object property using "@link Object" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getObject(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$type = Reflection_Property::getInstanceOf($class, $property)->getType()->asString();
			$value = Getter::getObject(null, $type, $object, $property);
			if (!is_object($value)) {
				$value = Builder::create($type);
			}
			$object->$property = $value;
		}
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after",
			'SAF\Framework\Autoloader->includeClass()',
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
		parent::registerProperties($class_name, "getter", "after", "read");
	}

	//---------------------------------------------------------------------------- registerGettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function registerGettersAop(AopJoinpoint $joinpoint)
	{
		if ($joinpoint->getReturnedValue()) {
			parent::registerProperties($joinpoint->getArguments()[0], "getter", "before", "read");
		}
	}

}
