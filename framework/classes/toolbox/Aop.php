<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/classes/reflection/Reflection_Class.php";

abstract class Aop
{

	//----------------------------------------------------------------------------------- $joinpoints
	static $joinpoints = array(); 

	//------------------------------------------------------------------------------- methodJoinpoint
	/**
	 * AopJoinpoint $joinpoint
	 */
	public static function propertyJoinpoint(AopJoinpoint $joinpoint)
	{
		static $antiloop = array();
		$class_name = $joinpoint->getClassName();
		$property_name = $joinpoint->getPropertyName();
		$call = self::$joinpoints[$class_name][$property_name];
		if (!isset($antiloop[$class_name]) && !isset($antiloop[$class_name][$property_name])) {
			$antiloop[$class_name][$property_name] = true;
			call_user_func(array($joinpoint->getObject(), $call), $joinpoint->getAssignedValue());
			unset($antiloop[$class_name][$property_name]);
		}
	}

	//---------------------------------------------------------------------------- registerProperties
	/**
	 * @param string $class_name
	 * @param string $annotation ie getter, setter
	 * @param string $function ie read, write
	 */
	public static function registerProperties($class_name, $annotation, $function)
	{
		if (@class_exists($class_name)) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->getProperties() as $property) {
				if ($property->class == $class_name) {
					$call = $property->getAnnotation($annotation)->value;
					if ($call) {
						if (substr($call, 0, 5) === "Aop::") {
							aop_add_before(
								$function . " " . $class_name . "->" . $property->name,
								array(get_called_class(), substr($call, 5))
							);
						}
						else {
							if ($class->getMethod($call)->isStatic()) {
								aop_add_after(
									$function . " " . $class_name . "->" . $property->name,
									array($class_name, $call)
								);
							} else {
								aop_add_after(
									$function . " " . $class_name . "->" . $property->name,
									array(__CLASS__, "propertyJoinpoint")
								);
								self::$joinpoints[$class_name][$property->name] = $call;
							}
						}
					}
				}
			}
		}
	}

}
