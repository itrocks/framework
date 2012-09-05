<?php
namespace SAF\Framework;

class Aop
{

	//------------------------------------------------------------------------------------ getterCall
	/**
	 * Register this for property needing a specific getter using
	 * aop_add_around("read Class_Name->property_name", "Aop::getterCall");
	 *
	 * The getter method name may :
	 * - be declared into the property's @getter annotation
	 * - default getter method for property $property_name must be named getPropertyName()
	 *
	 * The getter method must be private to avoid it to be directly called by programmers
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function getterCall($joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinpoint->getClassName();
			$getter = Reflection_Property::getInstanceOf($class, $property)->getGetter();
			$getter->setAccessible(true);
			$antiloop[$hash][$property] = true;
			$joinpoint->setReturnedValue($getter->invoke($object));
			unset($antiloop[$hash][$property]);
			$getter->setAccessible(false);
		} else {
			$joinpoint->process();
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
	 * @param AopJoinPoint $joinpoint
	 */
	public static function setterCall($joinpoint)
	{
		$object   = $joinpoint->getObject();
		$property = $joinpoint->getPropertyName();
		$hash     = spl_object_hash($object);
		static $antiloop = array();
		if (!isset($antiloop[$hash][$property])) {
			$class = $joinpoint->getClassName();
			$setter = Reflection_Property::getInstanceOf($class, $property)->getSetter();
			$setter->setAccessible(true);
			$antiloop[$hash][$property] = true;
			$setter->invoke($object, $setter($joinpoint->getAssignedValue()));
			unset($antiloop[$hash][$property]);
			$setter->setAccessible(false);
		} else {
			$joinpoint->process();
		}
	}

}
