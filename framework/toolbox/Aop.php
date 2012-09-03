<?php
namespace Framework;

class Aop
{

	//------------------------------------------------------------------------------ collectionGetter
	/**
	 * Register this for all objects collection fields using
	 * aop_add_before("read Class_Name->property_name", "Aop::collectionGetter");
	 *
	 * @param AopJoinPoint $joinPoint
	 */
	public static function collectionGetter($joinPoint)
	{
		$object   = $joinPoint->getTriggeringObject();
		$property = $joinPoint->getTriggeringPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinPoint->getTriggeringClassName();
			$antiloop[$hash][$property] = true;
			$value = $object->$property;
			unset($antiloop[$hash][$property]);
			if (!is_array($value)) {
				$type = rParse(Reflection_Property::getInstanceOf($class, $property)->getType(), ":");
				$object->$property = Getter::getCollection($value, $type, $object);
			}
		}
	}

	//-------------------------------------------------------------------------------- dateTimeGetter
	/**
	 * Register this for all DateTime fields using
	 * aop_add_before("read Class_Name->property_name", "Aop::dateTimeGetter");
	 *
	 * @param AopJoinPoint $joinPoint
	 */
	public static function dateTimeGetter($joinPoint)
	{
		$object   = $joinPoint->getTriggeringObject();
		$property = $joinPoint->getTriggeringPropertyName();
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

	//------------------------------------------------------------------------------------ getterCall
	/**
	 * Register this for property needing a specific getter using
	 * aop_add_around("read Class_Name->property_name", "Aop::getterCall");
	 *
	 * The getter method name may :
	 * - be declared into the property's @getter annotation
	 * - default getter method for property $property_name must be named getTriggeringPropertyName()
	 *
	 * The getter method must be private to avoid it to be directly called by programmers
	 *
	 * @param AopJoinPoint $joinPoint
	 */
	public static function getterCall($joinPoint)
	{
		$object   = $joinPoint->getTriggeringObject();
		$property = $joinPoint->getTriggeringPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinPoint->getTriggeringClassName();
			$getter = Reflection_Property::getInstanceOf($class, $property)->getGetter();
			$getter->setAccessible(true);
			$antiloop[$hash][$property] = true;
			$joinPoint->setReturnedValue($getter->invoke($object));
			unset($antiloop[$hash][$property]);
			$getter->setAccessible(false);
		} else {
			$joinPoint->process();
		}
	}

	//---------------------------------------------------------------------------------- objectGetter
	/**
	 * Register this for all object fields using
	 * aop_add_before("read Class_Name->property_name", "Aop::objectGetter");
	 *
	 * @param AopJoinPoint $joinPoint
	 */
	public static function objectGetter($joinPoint)
	{
		$object   = $joinPoint->getTriggeringObject();
		$property = $joinPoint->getTriggeringPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinPoint->getTriggeringClassName();
			$antiloop[$hash][$property] = true;
			$value = $object->$property;
			unset($antiloop[$hash][$property]);
			if (!is_object($value)) {
				$type = Reflection_Property::getInstanceOf($class, $property)->getType();
				$object->$property = Getter::getObject($value, $type);
			}
		}
	}

	//--------------------------------------------------------------------------------- registerAfter
	/**
	 * Register a call_back advice, called after the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerAfter($function, $call_back)
	{
		aop_add_after($function, $call_back);
	}

	//-------------------------------------------------------------------------------- registerAround
	/**
	 * Register a call_back advice, called around the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerAround($function, $call_back)
	{
		aop_add_around($function, $call_back);
	}

	//-------------------------------------------------------------------------------- registerBefore
	/**
	 * Register a call_back advice, called before the execution of a function or before
	 * the read/write of a property
	 *
	 * @param string $function ie "Class_Name->functionName()" or "Class_Name->property_name"
	 * @param string $call_back valid callback function descriptor : "Class_Name::functionName()
	 */
	public static function registerBefore($function, $call_back)
	{
		aop_add_before($function, $call_back);
	}

	//---------------------------------------------------------------------- registerCollectionGetter
	/**
	 * Register standard collection getter for a class property
	 * 
	 * @param string $class_property ie "Class_Name->property_name"
	 */
	public static function registerCollectionGetter($class_property)
	{
		aop_add_before("read $class_property", "Framework\\Aop::CollectionGetter");
	}

	//------------------------------------------------------------------------ registerDateTimeGetter
	/**
	 * Register standard Date_Time object getter for a class property
	 *
	 * This enable to read ISO string-based dates from a DAO for example,
	 * but you ever want to work with standard Date_Time objects for dates and times values.
	 *
	 * @param string $class_property ie "Class_Name->date_property_name"
	 */
	public static function registerDateTimeGetter($class_property)
	{
		aop_add_before("read $class_property", "Framework\\Aop::dateTimeGetter");
	}

	//-------------------------------------------------------------------------- registerObjectGetter
	/**
	 * Register standard object getter for a class property
	 *
	 * @param string $class_property ie "Class_Name->property_name"
	 */
	public static function registerObjectGetter($class_property)
	{
		aop_add_before("read $class_property", "Framework\\Aop::objectGetter");
	}

	//------------------------------------------------------------------------------------ setterCall
	/**
	 * Register this for property needing a specific setter using
	 * aop_add_around("write Class_Name->property_name", "Aop::setterCall");
	 *
	 * The setter method name may :
	 * - be declared into the property's @setter annotation
	 * - default setter method for property $property_name must be named setPropertyName($value)
	 *
	 * The setter method must be private to avoid it to be directly called by programmers
	 *
	 * @param AopJoinPoint $joinPoint
	 */
	public static function setterCall($joinPoint)
	{
		$object   = $joinPoint->getTriggeringObject();
		$property = $joinPoint->getTriggeringPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinPoint->getTriggeringClassName();
			$setter = Reflection_Property::getInstanceOf($class, $property)->getSetter();
			$setter->setAccessible(true);
			$antiloop[$hash][$property] = true;
			$setter->invoke($object, $setter($joinPoint->getAssignedValue()));
			unset($antiloop[$hash][$property]);
			$setter->setAccessible(false);
		} else {
			$joinPoint->process();
		}
	}

}
