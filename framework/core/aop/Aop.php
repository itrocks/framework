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

	//-------------------------------------------------------------------------------------- $advices
	/**
	 * Keys are : global counter (integer)
	 * values are advices callable : array("Class_Name"|$object, "methodName") | "functionName"
	 *
	 * @var array
	 */
	public static $advices = array();

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
	private static $joinpoints = array();

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
echo "ADD $when $function<br>";
		if (strpos($function, " ")) {
			list($what, $function) = explode(" ", $function, 1);
			$joinpoint = explode("->", $function, 1);
			switch ($what) {
				case "read":  self::addOnPropertyRead($joinpoint, $call_back);  break;
				case "write": self::addOnPropertyWrite($joinpoint, $call_back); break;
			}
		}
		elseif (strpos($function, "->")) {
			$joinpoint = explode("->", substr($function, 0, -2));
			switch ($when) {
				case Aop::AFTER:  self::addAfterMethodCall($joinpoint, $call_back);  break;
				case Aop::AROUND: self::addAroundMethodCall($joinpoint, $call_back); break;
				case Aop::BEFORE: self::addBeforeMethodCall($joinpoint, $call_back); break;
			}
		}
		else {
			$function = substr($function, 0, -2);
			switch ($when) {
				case Aop::AFTER:  self::addAfterFunctionCall($function, $call_back);  break;
				case Aop::AROUND: self::addAroundFunctionCall($function, $call_back); break;
				case Aop::BEFORE: self::addBeforeFunctionCall($function, $call_back); break;
			}
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
	 * @return integer
	 */
	public static function addAfterFunctionCall($joinpoint, $advice)
	{
		$code = '
			$_joinpoint = new SAF\Framework\After_Function_Joinpoint("$joinpoint", $advice_string);
			$result = isset($result) ? $result : _$joinpoint_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		return self::addFunctionCall($joinpoint, $advice, $code);
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
	 * @return integer|integer[]
	 */
	public static function addAfterMethodCall($joinpoint, $advice)
	{
		$code = '
			$object = $this;
			$joinpoint = new SAF\Framework\After_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string);
			$result = isset($result) ? $result : $this->_$joinpoint[1]_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		return self::addMethodCall($joinpoint, $advice, $code);
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
	 * @return integer
	 */
	public static function addAroundFunctionCall($joinpoint, $advice)
	{
		$code = '
			$joinpoint = new SAF\Framework\Around_Function_Joinpoint("$joinpointF", $advice_string, "_$joinpointF_$count");
			return call_user_func_array($advice_string, array($advice_arguments));
		';
		return self::addFunctionCall($joinpoint, $advice, $code);
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
	 * @return integer|integer[]
	 */
	public static function addAroundMethodCall($joinpoint, $advice)
	{
		$code = '
			$object = $this;
			$joinpoint = new SAF\Framework\Around_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string, array($this, "_$joinpoint[1]_$count"));
			return call_user_func_array($advice_string, array($advice_arguments));
		';
		return self::addMethodCall($joinpoint, $advice, $code);
	}

	//------------------------------------------------------------------------- addBeforeFunctionCall
	/**
	 * Launch an advice before the execution of a given function
	 *
	 * Advice arguments are the pointcut object, then the arguments passed to the joinpoint function.
	 * The advice can return a value : if this value is set, the execution of the joinpoint will be
	 * cancelled and the returned value replaced by this one.
	 *
	 * @param $joinpoint string the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice    string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer
	 */
	public static function addBeforeFunctionCall($joinpoint, $advice)
	{
		$code = '
			$_joinpoint = new SAF\Framework\Before_Function_Joinpoint("$joinpoint", $advice_string);
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : _$joinpoint_$count($process_arguments);
		';
		return self::addFunctionCall($joinpoint, $advice, $code);
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
	 * @return integer|integer[]
	 */
	public static function addBeforeMethodCall($joinpoint, $advice)
	{
		$code = '
			$object = $this;
			$joinpoint = new SAF\Framework\Before_Method_Joinpoint(__CLASS__, $this, "$joinpoint[1]", $advice_string);
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : $this->_$joinpoint[1]_$count($process_arguments);
		';
		return self::addMethodCall($joinpoint, $advice, $code);
	}

	//------------------------------------------------------------------------------- addFunctionCall
	/**
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $code string
	 * @return integer
	 */
	private static function addFunctionCall($joinpoint, $advice, $code)
	{
		$count = count(self::$joinpoints);

		$arguments = (new Reflection_Function($joinpoint))->getParameters();
		$arguments_names = array_keys($arguments);
		$method_arguments = join(", ", $arguments);

		$process_arguments = $arguments ? ('$' . join(', $', $arguments_names)) : '';

		$advice_string = self::callbackString($advice, $count);
		$advice_method = is_array($advice)
			? new Reflection_Method($advice[0], $advice[1])
			: new Reflection_Function($advice);
		$advice_arguments_names = array_keys($advice_method->getParameters());
		$advice_arguments = ($advice_arguments_names) ? join(', &$', $advice_arguments_names) : '';

		$code = str_replace(
			array('$advice_string', '$joinpointF', '$count', '$advice_arguments', '$process_arguments'),
			array($advice_string, $joinpoint, $count, $advice_arguments, $process_arguments),
			$code
		);

		if (!runkit_function_rename($joinpoint, "_" . $joinpoint . "_" . $count)) {
			user_error("Could not rename $joinpoint to _{$joinpoint}_$count", E_USER_ERROR);
		}
		if (!runkit_function_add($joinpoint, $method_arguments, $code)) {
			user_error("Could not add method $joinpoint($process_arguments)", E_USER_ERROR);
		}
		self::$advices[$count] = $advice;
		self::$joinpoints[$count] = $joinpoint;
		return $count;
	}

	//--------------------------------------------------------------------------------- addMethodCall
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice string[]|object[]|string the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $code string
	 * @return integer|integer[]
	 */
	private static function addMethodCall($joinpoint, $advice, $code)
	{
		$counts = array();
		$trait = new Reflection_Class($joinpoint[0]);
		if ($trait->isTrait()) {
			foreach ($trait->getDeclaredClassesUsingTrait() as $class) {
				$counts[] = self::addBeforeMethodCall(array($class->name, $joinpoint[1]), $advice);
			}
		}
		$count = count(self::$joinpoints);

		$method = (new Reflection_Method($joinpoint[0], $joinpoint[1]));
		$arguments = $method->getParameters();
		$method_arguments = join(", ", $arguments);

		$process_arguments = $arguments ? ('$' . join(', $', array_keys($arguments))) : '';

		$advice_string = self::callbackString($advice, $count);
		$advice_method = is_array($advice)
			? new Reflection_Method($advice[0], $advice[1])
			: new Reflection_Function($advice);
		$advice_arguments_names = array_keys($advice_method->getParameters());
		$advice_arguments = ($advice_arguments_names)
			? ('&$' . join(', &$', $advice_arguments_names))
			: '';

		$code = str_replace(
			array(
				'$advice_string', '$joinpoint[1]', '$count', '$advice_arguments', '$process_arguments',
				'__CLASS__'
			),
			array(
				$advice_string, $joinpoint[1], $count, $advice_arguments, $process_arguments,
				"'$joinpoint[0]'"
			),
			$code
		);

		if (!runkit_method_rename($joinpoint[0], $joinpoint[1], "_" . $joinpoint[1] . "_" . $count)) {
			user_error(
				"Could not rename $joinpoint[0]::$joinpoint[1] to _$joinpoint[1]_$count", E_USER_ERROR
			);
		}
		$acc = 0;
		if ($method->isPublic())    $acc |= RUNKIT_ACC_PUBLIC;
		if ($method->isProtected()) $acc |= RUNKIT_ACC_PROTECTED;
		if ($method->isPrivate())   $acc |= RUNKIT_ACC_PRIVATE;
		if ($method->isStatic()) {
			$acc |= RUNKIT_ACC_STATIC;
			$code = str_replace(array('$this->', '$this'), array('self::', 'null'), $code);
		}
echo "$joinpoint[0]::$joinpoint[1]<br>";
echo "<pre>$method_arguments : " . print_r($code, true) . "</pre>";
		if (!runkit_method_add($joinpoint[0], $joinpoint[1], $method_arguments, $code, $acc)) {
			user_error(
				"Could not add method $joinpoint[0]::$joinpoint[1]($process_arguments)", E_USER_ERROR
			);
		}
		self::$advices[$count] = $advice;
		self::$joinpoints[$count] = $joinpoint;
		return $counts ? ($counts + array($count)) : $count;
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
						$_property_name = "_" . $property_name;
						$this->$_property_name = $this->$property_name;
						unset($this->$property_name);
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
						if (is_array($advice) && isset($advice["dynamic"])) {
							$advice[0] = $this;
							unset($advice["dynamic"]);
						}
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
				if ($property[0] == "_") {
					$this->$property = $value;
					return;
				}
				$_property = "_" . $property;
				if (isset(' . $aop_properties . '[$property]["write"])) {
					$joinpoint = new SAF\Framework\Property_Write_Joinpoint("' . $joinpoint[0] . '", $this, $property);
					foreach (' . $aop_properties . '[$property]["write"] as $advice) {
						if (is_array($advice) && isset($advice["dynamic"])) {
							$advice[0] = $this;
							unset($advice["dynamic"]);
						}
						$joinpoint->advice = $advice;
						$value = call_user_func_array($advice, array($value, $joinpoint));
					}
				}
				$this->$_property = $value;
			');
			runkit_method_add($class_name, '__unset', '$property', '
				if ($property[0] != "_") $property = "_" . $property;
				unset($this->$property);
			');
		}
		if (!isset(self::$properties[$class_name][$property_name][$side])) {
			self::$properties[$class_name][$property_name][$side] = array();
		}
		if (
			is_array($advice)
			&& is_string($advice[0])
			&& !(new Reflection_Method($advice[0], $advice[1]))->isStatic()
		) {
			$advice["dynamic"] = true;
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
				? "array(" . __CLASS__ . "::\$advices[$count][0], '$callback[1]')"
				: "array('$callback[0]', '$callback[1]')"
			);
	}

	//---------------------------------------------------------------------------- registerProperties
	/**
	 * @param $class_name string
	 * @param $annotation string ie "getter", "setter"
	 * @param $function   string ie "read", "write"
	 */
	public static function registerProperties($class_name, $annotation, $function)
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
						}
						switch ($function) {
							case Aop::READ:
								Aop::addOnPropertyRead(
									array($class_name, $property->name), array($call_class, $call_method)
								);
								break;
							case Aop::WRITE:
								Aop::addOnPropertyWrite(
									array($class_name, $property->name), array($call_class, $call_method)
								);
								break;
						}
					}
				}
			}
		}
		else {
			echo "- DEAD CODE : Register properties for non existing class $class_name : $annotation<br>";
		}
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an AOP link, knowing its handler returned when calling the add* methods
	 *
	 * @param $handler integer|integer[]
	 */
	public static function remove($handler)
	{
		if (isset($handler)) {
			if (is_array($handler)) {
				foreach ($handler as $count) {
					self::remove($count);
				}
			}
			else {
				$joinpoint = self::$joinpoints[$handler];
				if (is_array($joinpoint)) {
					runkit_method_remove($joinpoint[0], $joinpoint[1]);
					runkit_method_rename($joinpoint[0], "_" . $joinpoint[1] . "_" . $handler, $joinpoint[1]);
				}
				else {
					runkit_function_remove($joinpoint);
					runkit_function_rename("_" . $joinpoint . "_" . $handler, $joinpoint);
				}
				self::$joinpoints[$handler] = null;
			}
		}
	}

}
