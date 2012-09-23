<?php
namespace SAF\Framework;

require_once "framework/classes/reflection/Reflection_Class.php";

abstract class Aop
{

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
					$getter = $property->getAnnotation($annotation)->value;
					if ($getter) {
						if (substr($getter, 0, 5) === "Aop::") {
							Aop::registerBefore(
									$function . " " . $class_name . "->" . $property->name,
									array(__CLASS__, substr($getter, 5))
							);
						}
						else {
							Aop::registerAround(
									$function . " " . $class_name . "->" . $property->name,
									array($class_name, $getter)
							);
						}
					}
				}
			}
		}
	}

}
