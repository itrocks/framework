<?php
namespace SAF\Framework;

use AopJoinpoint;

/**
 * Aop calls getters
 */
abstract class Aop_Getter extends Aop implements Plugin
{

	//---------------------------------------------------------------------------------------- getAll
	/**
	 * Register this using "@getter Aop::getAll" for a property that points on all existing elements of a collection
	 *
	 * @param AopJoinpoint $joinpoint
	 * @return object[]
	 */
	public static function getAll(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$type_name = (new Reflection_Property($class, $property))->getType()
				->getElementTypeAsString($class);
			$object->$property = Getter::getAll(null, $type_name);
		}
		return $object->$property;
	}

	//--------------------------------------------------------------------------------- getCollection
	/**
	 * Register this for any object collection property using "@link Collection" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 * @return Component[]
	 */
	public static function getCollection(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$type_name = (new Reflection_Property($class, $property))->getType()
				->getElementTypeAsString();
			$object->$property = Getter::getCollection(null, $type_name, $object, $property);
		}
		return $object->$property;
	}

	//----------------------------------------------------------------------------------- getDateTime
	/**
	 * Register this for any Date_Time property using "@link DateTime" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 * @return Date_Time
	 */
	public static function getDateTime(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$value = $object->$property;
		if (is_string($value)) {
			$object->$property = Date_Time::fromISO($value);
		}
		return $object->$property;
	}

	//--------------------------------------------------------------------------------------- getFile
	/**
	 * Register this for any object property using "@link File" annotation
	 *
	 * @param $joinpoint AopJoinpoint
	 */
	public static function getFile(AopJoinpoint $joinpoint)
	{
		return self::getObject($joinpoint);
	}

	//---------------------------------------------------------------------------------------- getMap
	/**
	 * Register this for any object map property using "@link Map" annotation
	 *
	 * @param AopJoinpoint $joinpoint
	 * @return object[]
	 */
	public static function getMap(AopJoinpoint $joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		if (!isset($object->$property)) {
			$class = $joinpoint->getClassName();
			$property = new Reflection_Property($class, $property);
			$object->$property = Getter::getMap(null, $object, $property);
		}
		return $object->$property;
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
			$property = new Reflection_Property($class, $property);
			$type = $property->getType()->asString();
			$value = Getter::getObject(null, $type, $object, $property->name);
			if (!is_object($value)) {
				$value = Null_Object::create($type);
			}
			$property->setValue($object, $value);
		}
		return $object->$property;
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\Autoloader', "includeClass"),
			array(__CLASS__, "registerIncludedGettersAop")
		);
		Aop::addAfterMethodCall(
			array('SAF\Framework\Class_Builder', "buildClassSource"),
			array(__CLASS__, "registerBuiltGettersAop")
		);
	}

	//----------------------------------------------------------------------- registerBuiltGettersAop
	/**
	 * AOP auto-registerer call
	 *
	 * @param $class_name string
	 */
	public static function registerBuiltGettersAop($class_name)
	{
		parent::registerProperties($class_name, "getter", "read");
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

	//-------------------------------------------------------------------- registerIncludedGettersAop
	/**
	 * AOP auto-register call
	 *
	 * @param $class_name string
	 * @param $result     string
	 */
	public static function registerIncludedGettersAop($class_name, $result)
	{
		if ($result) {
			$class_name = Autoloader::rectifyClassName($class_name, $result);
			parent::registerProperties($class_name, "getter", "read");
		}
	}

}
