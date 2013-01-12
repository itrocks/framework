<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/core/reflection/Reflection_Class.php";

abstract class Aop
{

	//-------------------------------------------------------------------------- $property_joinpoints
	private static $property_joinpoints = array();

	//------------------------------------------------------------------------------------------- add
	/**
	 * Launch advice $call_back after the execution of the joinpoint function $function
	 *
	 * @param string $where when to do the capture : "after", "after_returning", "after_throwing", "around", "before"
	 * @param string $function can be "functionName()" or "Class_Name->methodName()"
	 *   or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
	 * @param mixed  $call_back string(function name) or array(class name or object, method)
	 *   or function as a closure
	 */
	public static function add($when, $function, $call_back)
	{
		$aop_call = "aop_add_" . $when;
		$class_name = strpos($function, "->") ? substr($function, 0, strpos($function, "->")) : null;
		if ($i = strrpos($class_name, " ")) $class_name = substr($class_name, $i + 1);
		if (isset($class_name) && (substr($function, -2) === "()")) {
			$aop_call($function, function(AopJoinpoint $joinpoint) use ($call_back, $class_name, $when) {
				// TODO this test is not complete : test all cases for herited methods
				if (
					($joinpoint->getClassName() === $class_name)
					|| (get_class($joinpoint->getObject()) === $class_name)
					|| (
						Reflection_Method::getInstanceOf($class_name, $joinpoint->getMethodName())->class
						=== $class_name
					)
				) {
					call_user_func($call_back, $joinpoint);
				}
				else {
					if ($when == "around") {
						$joinpoint->process();
					}
				}
			});
		}
		else {
			$aop_call($function, $call_back);
		}
	}

	//----------------------------------------------------------------------------- propertyJoinpoint
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function propertyJoinpoint(AopJoinpoint $joinpoint)
	{
		static $antiloop = array();
		$class_name = $joinpoint->getClassName();
		$property_name = $joinpoint->getPropertyName();
		$call = self::$property_joinpoints[$class_name][$property_name];
		if (!isset($antiloop[$class_name]) && !isset($antiloop[$class_name][$property_name])) {
			$antiloop[$class_name][$property_name] = true;
			if ($joinpoint->getKindOfAdvice() & AOP_KIND_WRITE) {
				call_user_func(array($joinpoint->getObject(), $call), $joinpoint->getAssignedValue());
			}
			else {
				call_user_func(array($joinpoint->getObject(), $call));
			}
			unset($antiloop[$class_name][$property_name]);
		}
	}

	//---------------------------------------------------------------------------- registerProperties
	/**
	 * @param string $class_name
	 * @param string $annotation ie "getter", "setter"
	 * @param string $function ie "read", "write"
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
							Aop::add("before",
								$function . " " . $class_name . "->" . $property->name,
								array(get_called_class(), substr($call, 5))
							);
						}
						else {
							if ($class->getMethod($call)->isStatic()) {
								Aop::add("after",
									$function . " " . $class_name . "->" . $property->name,
									array($class_name, $call)
								);
							} else {
								Aop::add("after",
									$function . " " . $class_name . "->" . $property->name,
									array(__CLASS__, "propertyJoinpoint")
								);
								self::$property_joinpoints[$class_name][$property->name] = $call;
							}
						}
					}
				}
			}
		}
	}

}
