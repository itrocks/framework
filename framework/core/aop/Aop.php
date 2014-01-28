<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/After_Method_Joinpoint.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Around_Method_Joinpoint.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Before_Method_Joinpoint.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Property_Read_Joinpoint.php";
/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Property_Write_Joinpoint.php";

use AopJoinpoint;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/reflection/Reflection_Class.php";

/**
 * The Aop class is an interface to the Aop calls manager
 */
abstract class Aop
{

	//----------------------------------------------------------------------------------------- AFTER
	const AFTER = "after";

	//---------------------------------------------------------------------------------------- AROUND
	const AROUND = "around";

	//---------------------------------------------------------------------------------------- BEFORE
	const BEFORE = "before";

	//------------------------------------------------------------------------------------------ READ
	const READ = "read";

	//----------------------------------------------------------------------------------------- WRITE
	const WRITE = "write";

	//------------------------------------------------------------------------------------- $antiloop
	private static $antiloop = array();

	//----------------------------------------------------------------------------------- $properties
	public static $properties = array();

	//--------------------------------------------------------------------------------------- $ignore
	/**
	 * If true, the aop class's calls are ignored to avoid side effects
	 * Don't forget to bring it back to false when you're done !
	 *
	 * @var boolean
	 */
	public static $ignore = false;

	//----------------------------------------------------------------------------------- $joinpoints
	/**
	 * Keys are : global counter (integer)
	 * values are advices callback : array(kind, advice)
	 * @var array
	 */
	public static $joinpoints = array();

	//------------------------------------------------------------------------- $joinpoints_by_method
	/**
	 * Keys are : class name, method name
	 * values are advices callback : array(kind, advice)
	 * @var array
	 */
	public static $methods_joinpoints = array();

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
					true
					|| ($joinpoint->getClassName() === $class_name)
					|| (get_class($joinpoint->getObject()) === $class_name)
					|| (
						(new Reflection_Method($class_name, $joinpoint->getMethodName()))->class == $class_name
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

	//-------------------------------------------------------------------------- addAfterFunctionCall
	/**
	 * Launch an advice after the execution of a given function
	 *
	 * Advices arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addAfterFunctionCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\After_Function_Joinpoint("$joinpoint", $advice_string);
			$result = isset($result) ? $result : _$joinpoint_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		self::addFunctionCall($joinpoint, $advice, $code, true);
	}

	//---------------------------------------------------------------------------- addAfterMethodCall
	/**
	 * Launch an advice after the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * If set, the value returned by the advice will be the pointcut returned value.
	 * If not set, the result value passed as argument (that can be modified) will be returned
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addAfterMethodCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\After_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string);
			$result = isset($result) ? $result : $this->_$joinpoint[1]_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		self::addMethodCall($joinpoint, $advice, $code, true);
	}

	//------------------------------------------------------------------------- addAroundFunctionCall
	/**
	 * Launch an advice instead of the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addAroundFunctionCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\Around_Function_Joinpoint("$joinpoint", $advice_string, "_$joinpoint_$count");
			return call_user_func_array($advice_string, array($advice_arguments));
		';
		self::addFunctionCall($joinpoint, $advice, $code, false);
	}

	//--------------------------------------------------------------------------- addAroundMethodCall
	/**
	 * Launch an advice instead of the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function,
	 * and finally the value returned by the joinpoint method call.
	 * The value returned by the advice will be the pointcut returned value.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addAroundMethodCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\Around_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string, array($this, "_$joinpoint[1]_$count"));
			return call_user_func_array($advice_string, array($advice_arguments));
		';
		self::addMethodCall($joinpoint, $advice, $code, false);
	}

	//------------------------------------------------------------------------- addBeforeFunctionCall
	/**
	 * Launch an advice before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addBeforeFunctionCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\Before_Function_Joinpoint("$joinpoint", $advice_string);
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : _$joinpoint_$count($process_arguments);
		';
		self::addFunctionCall($joinpoint, $advice, $code, false);
	}

	//--------------------------------------------------------------------------- addBeforeMethodCall
	/**
	 * Launch an advice before the execution of a given method
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint method
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addBeforeMethodCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\Before_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string);
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : $this->_$joinpoint[1]_$count($process_arguments);
		';
		self::addMethodCall($joinpoint, $advice, $code, false);
	}

	//------------------------------------------------------------------------------- addFunctionCall
	/**
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $result_as_arg boolean
	 * @param $code string
	 */
	private static function addFunctionCall($joinpoint, $advice, $code, $result_as_arg)
	{
		$count = count(self::$joinpoints);
		$arguments = (new Reflection_Function($joinpoint))->getParameters();
		$arguments_names = array_keys($arguments);
		$method_arguments = join(", ", $arguments);

		$advice_string = self::callbackString($advice, $count);
		$advice_arguments = ($arguments ? ('&$' . join(', &$', $arguments_names)) : '');
		$process_arguments = $arguments ? ('$' . join(', $', $arguments_names)) : '';
		if ($result_as_arg) {
			if ($advice_arguments) $advice_arguments .= ", ";
			$advice_arguments .= '&$result, $joinpoint';
			if ($process_arguments) $process_arguments .= ", ";
			$process_arguments .= '$result, $joinpoint';
		}
		else {
			if ($advice_arguments) $advice_arguments .= ", ";
			$advice_arguments .= '$joinpoint';
			if ($process_arguments) $process_arguments .= ", ";
			$process_arguments .= '$joinpoint';
		}

		$code = str_replace(
			array('$advice_string', '$joinpoint', '$count', '$advice_arguments', '$process_arguments'),
			array($advice_string, $joinpoint, $count, $advice_arguments, $process_arguments),
			$code
		);

		if (!runkit_function_rename($joinpoint, "_" . $joinpoint . "_" . $count)) {
			user_error("Could not rename $joinpoint to _{$joinpoint}_$count", E_USER_ERROR);
		}
		if (!runkit_function_add($joinpoint, $method_arguments, $code)) {
			user_error("Could not add method $joinpoint($arguments)", E_USER_ERROR);
		}
		self::$joinpoints[$count] = $advice;
	}

	//--------------------------------------------------------------------------------- addMethodCall
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $result_as_arg boolean
	 * @param $code string
	 */
	private static function addMethodCall($joinpoint, $advice, $code, $result_as_arg)
	{
		$trait = new Reflection_Class($joinpoint[0]);
		if ($trait->isTrait()) {
			foreach ($trait->getDeclaredClassesUsingTrait() as $class) {
				self::addBeforeMethodCall(array($class->name, $joinpoint[1]), $advice);
			}
		}
		else {
			$count = count(self::$joinpoints);
			$arguments = (new Reflection_Method($joinpoint[0], $joinpoint[1]))->getParameters();
			$arguments_names = array_keys($arguments);
			$method_arguments = join(", ", $arguments);

			$advice_string = self::callbackString($advice, $count);
			$advice_arguments = '$this' . ($arguments ? (', &$' . join(', &$', $arguments_names)) : '');
			$process_arguments = $arguments ? ('$' . join(', $', $arguments_names)) : '';
			if ($result_as_arg) {
				$advice_arguments .= ', &$result, $joinpoint';
				if ($process_arguments) $process_arguments .= ", ";
				$process_arguments .= '$result, $joinpoint';
			}
			else {
				$advice_arguments .= ', $joinpoint';
				if ($process_arguments) $process_arguments .= ", ";
				$process_arguments .= '$joinpoint';
			}

			$code = str_replace(
				array('$advice_string', '$joinpoint[1]', '$count', '$advice_arguments', '$process_arguments', '__CLASS__'),
				array($advice_string, $joinpoint[1], $count, $advice_arguments, $process_arguments, "'$joinpoint[0]'"),
				$code
			);

			if (!runkit_method_rename($joinpoint[0], $joinpoint[1], "_" . $joinpoint[1] . "_" . $count)) {
				user_error(
					"Could not rename $joinpoint[0]::$joinpoint[1] to _$joinpoint[1]_$count", E_USER_ERROR
				);
			}
			if (!runkit_method_add($joinpoint[0], $joinpoint[1], $method_arguments, $code)) {
				user_error(
					"Could not add method $joinpoint[0]::$joinpoint[1]($arguments)", E_USER_ERROR
				);
			}
			self::$joinpoints[$count] = $advice;
		}
	}

	//--------------------------------------------------------------------------------- addOnProperty
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "property_name")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $side      string Aop::READ or Aop::WRITE
	 */
	private static function addOnProperty($joinpoint, $advice, $side)
	{
		list($class_name, $property_name) = $joinpoint;
		if (!isset(Aop::$properties[$class_name])) {
			$aop_properties = __CLASS__ . "::\$properties['$class_name']";
			if (!method_exists($class_name, '__aop')) {
				// magic method __aop : initializes aop, must be called on beginning of __construct
				runkit_method_add($class_name, '__aop', '', '
					foreach (array_keys(' . $aop_properties . ') as $property_name) {
						if (isset($this->$property_name)) {
							$_property_name = "_" . $property_name;
							$this->$_property_name = $this->$property_name;
							unset($this->$property_name);
						}
					}
				');
				// currently existing constructor renamed as __construct_aop
				if (method_exists($class_name, '__construct')) {
					$__construct = new Reflection_Method($class_name, '__construct');
					$arguments = $__construct->getParameters();
					$method_arguments = join(", ", $arguments);
					$process_arguments = join(", ", array_keys($arguments));
					if ($class_name == $__construct->class) {
						runkit_method_rename($class_name, '__construct', '__construct_aop');
						$construct_call = "\n" . "self::__construct_aop($process_arguments);";
					}
					else {
						$construct_call = "\n" . "parent::__construct($process_arguments)";
					}
				}
				else {
					$method_arguments = "";
					$construct_call = "";
				}
				runkit_method_add(
					$class_name, '__construct', $method_arguments,
					"self::__aop();" . $construct_call
				);
			}
			// set magic methods for AOP
			runkit_method_add($class_name, '__get', '$property', '
				if ($property[0] == "_") return null;
				$_property = "_" . $property;
				$value = isset($this->$_property) ? $this->$_property : null;
				if (isset(' . $aop_properties . '[$property]["read"])) {
					$joinpoint = new SAF\Framework\Property_Read_Joinpoint("' . $joinpoint[0] . '", $this, $property);
					foreach (' . $aop_properties . '[$property]["read"] as $advice) {
						$joinpoint->advice = $advice;
						$value = call_user_func_array($advice, array($value, $joinpoint));
					}
				}
				return $value;
			');
			runkit_method_add($class_name, '__isset', '$property', '
				if ($property[0] == "_") return false;
				$_property = "_" . $property;
				return isset($this->$_property);
			');
			runkit_method_add($class_name, '__set', '$property, $value', '
				if ($property[0] != "_") $property = "_" . $property;
				if (isset(' . $aop_properties . '[$property]["write"])) {
					$joinpoint = new SAF\Framework\Property_Write_Joinpoint("' . $joinpoint[0] . '", $this, $property);
					foreach (' . $aop_properties . '[$property]["write"] as $advice) {
						$joinpoint->advice = $advice;
						$value = call_user_func_array($advice, array($value, $joinpoint));
					}
				}
				$this->$property = $value;
			');
			runkit_method_add($class_name, '__unset', '$property', '
				if ($property[0] != "_") $property = "_" . $property;
				unset($this->$property);
			');
		}
		if (!isset(self::$properties[$class_name][$property_name][$side])) {
			self::$properties[$class_name][$property_name][$side] = array();
		}
		array_unshift(self::$properties[$class_name][$property_name][$side], $advice);
	}

	//----------------------------------------------------------------------------- addOnPropertyRead
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "property_name")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addOnPropertyRead($joinpoint, $advice)
	{
		self::addOnProperty($joinpoint, $advice, Aop::READ);
	}

	//---------------------------------------------------------------------------- addOnPropertyWrite
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "property_name")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 */
	public static function addOnPropertyWrite($joinpoint, $advice)
	{
		self::addOnProperty($joinpoint, $advice, Aop::WRITE);
	}

	//-------------------------------------------------------------------------------- callbackString
	/**
	 * @param $callback string[]|object[]|string
	 * @param $count    integer
	 * @return string
	 */
	private static function callbackString($callback, $count)
	{
		return is_string($callback)
			? ("'" . $callback . "'")
			: (
				(is_object($callback[0]))
				? "array(" . __CLASS__ . "::\$joinpoints[$count][0], '$callback[1]')"
				: "array('$callback[0]', '$callback[1]')"
			);
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
		if (
			($is_class = @class_exists($class_name, false))
			|| @trait_exists($class_name, false)
			|| @interface_exists($class_name, false)
		) {
			$class = new Reflection_Class($class_name);
			// properties overridden in traits must be overridden into final class
			$overridden_properties = array();
			if ($is_class) {
				foreach ($class->getListAnnotations("override") as $override) {
					/** @var $override Class_Override_Annotation */
					foreach ($override->values() as $overridden_annotation => $override_value) {
						if (in_array($overridden_annotation, array("getter", "link", "setter"))) {
							$overridden_properties[$override->property_name] = true;
						}
					}
				}
			}
			// define getter / setter for each property
			foreach ($class->getProperties() as $property) {
				if (($property->class == $class_name) || isset($overridden_properties[$property->name])) {
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
							if (!self::$ignore) {
								$object   = $joinpoint->getObject();
								$property = $joinpoint->getPropertyName();
								$hash     = spl_object_hash($object) . $when . $function . $property;
								if (!isset(Aop::$antiloop[$hash])) {
									Aop::$antiloop[$hash] = true;
									if ($static) {
										// static reader / writer
										$value = call_user_func(array($call_class, $call_method), $joinpoint);
										if (isset($value)) {
											$joinpoint->setReturnedValue($value);
										}
									}
									elseif ($joinpoint->getKindOfAdvice() & AOP_KIND_READ) {
										// dynamic reader
										$value = call_user_func(array($object, $call_method));
										if (isset($value)) {
											$joinpoint->setReturnedValue($value);
										}
									}
									else {
										// dynamic writer
										call_user_func(array($object, $call_method), $joinpoint->getAssignedValue());
									}
									unset(Aop::$antiloop[$hash]);
								}
							}
						};
						Aop::add(
							$when, $function . " " . $class_name . "->" . $property->name, $antiloopCall
						);
					}
				}
			}
		}
		else {
			echo "- DEAD CODE : Register properties for non existing class $class_name : $annotation<br>";
		}
	}

}
