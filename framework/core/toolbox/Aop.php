<?php
namespace SAF\Framework;
use AopJoinpoint;

/** @noinspection PhpIncludeInspection called from index.php */
require_once "framework/core/reflection/Reflection_Class.php";

abstract class Aop
{

	//------------------------------------------------------------------------------------- $antiloop
	private static $antiloop = array();

	//------------------------------------------------------------------------------------------- add
	/**
	 * Launch advice $call_back after the execution of the joinpoint function $function
	 *
	 * @param $when     string when to do the capture : "after", "after_returning", "after_throwing", "around", "before"
	 * @param $function string can be "functionName()" or "Class_Name->methodName()" or "Class_Name->property_name". May contain joker * characters or be prefixed by NameSpace\.
	 * @param $call_back string|array|mixed function name, array(class name or object, method) or function as a closure
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

	//---------------------------------------------------------------------------- registerProperties
	/**
	 * @param $class_name string
	 * @param $annotation string ie "getter", "setter"
	 * @param $when       string ie "after", "around", "before"
	 * @param $function   string ie "read", "write"
	 */
	public static function registerProperties($class_name, $annotation, $when, $function)
	{
		if (@class_exists($class_name)) {
			$class = Reflection_Class::getInstanceOf($class_name);
			foreach ($class->getProperties() as $property) {
				if ($property->class == $class_name) {
					$call = $property->getAnnotation($annotation)->value;
					if ($call) {
						if (strpos($call, "::")) {
							$static = true;
							if (substr($call, 0, 5) === "Aop::") {
								$call_class  = get_called_class();
								$call_method = substr($call, 5);
							}
							else {
								list($call_class, $call_method) = explode("::", $call);
							}
						}
						else {
							$call_class  = $class_name;
							$call_method = $call;
							$static = $class->getMethod($call_method)->isStatic();
						}
						$antiloopCall = function(AopJoinpoint $joinpoint)
							use($call_class, $call_method, $function, $static, $when)
						{
							$object   = $joinpoint->getObject();
							$property = $joinpoint->getPropertyName();
							$hash     = spl_object_hash($object) . $when . $function . $property;
							if (!isset(Aop::$antiloop[$hash])) {
								Aop::$antiloop[$hash] = true;
								call_user_func(array($static ? $call_class : $object, $call_method), $joinpoint);
								unset(Aop::$antiloop[$hash]);
							}
						};
						Aop::add(
							$when, $function . " " . $class_name . "->" . $property->name, $antiloopCall
						);
					}
				}
			}
		}
	}

}
