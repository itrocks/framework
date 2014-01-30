<?php
namespace SAF\Framework;

/**
 * The Aop class is an interface to the Aop calls manager
 */
abstract class Aop
{

	//----------------------------------------------------------------------------------------- DEBUG
	/** @todo remove this DEBUG const and echo when done */
	const DEBUG = false;

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
	 * @var callable[]
	 */
	public static $advices = array();

	//----------------------------------------------------------------------------------- $properties
	/**
	 * @var array
	 */
	public static $properties = array();

	//----------------------------------------------------------------------------------- $joinpoints
	/**
	 * Keys are : global counter (integer)
	 * Values are pointcuts callable : array("Class_Name", "methodName")
	 *
	 * @var callable[]
	 */
	private static $joinpoints = array();

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
		echo "@deprecated call : Aop::add($when, $function)<br>";
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
	 * @param $advice    callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer
	 */
	public static function addAfterFunctionCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "after ";
		$code = '
			$result = $joinpointF_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		$joinpoint_code = '
				new SAF\Framework\After_Function_Joinpoint(
					"$joinpointF", $parameters_string, $result, $advice_string
				)
			';
		return self::addFunctionCall($joinpoint, $advice, $code, $joinpoint_code);
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer|integer[]
	 */
	public static function addAfterMethodCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "after ";
		$code = '
			$result = $this->_$joinpointM_$count($process_arguments);
			$result2 = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result2) ? $result2 : $result;
		';
		$joinpoint_code = '
				new SAF\Framework\After_Method_Joinpoint(
					__CLASS__, array($this, "$joinpointM"), $parameters_string, $result, $advice_string
				)
			';
		return self::addMethodCall($joinpoint, $advice, $code, $joinpoint_code);
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer
	 */
	public static function addAroundFunctionCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "around ";
		$code = '
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : $joinpoint->result;
		';
		$joinpoint_code = '
				$joinpoint = new SAF\Framework\Around_Function_Joinpoint(
					"$joinpointF", $parameters_string, $advice_string, "$joinpointF_$count"
				)
			';
		return self::addFunctionCall($joinpoint, $advice, $code, $joinpoint_code);
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer|integer[]
	 */
	public static function addAroundMethodCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "around ";
		$code = '
			$result = call_user_func_array($advice_string, array($advice_arguments));
			return isset($result) ? $result : $joinpoint->result;
		';
		$joinpoint_code = '
				$joinpoint = new SAF\Framework\Around_Method_Joinpoint(
					__CLASS__, array($this, "$joinpointM"), $parameters_string, $advice_string,
					"_$joinpointM_$count"
				)
			';
		return self::addMethodCall($joinpoint, $advice, $code, $joinpoint_code);
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer
	 */
	public static function addBeforeFunctionCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "before ";
		$code = '
			$result = call_user_func_array($advice_string, array($advice_arguments));
			if (!isset($result)) $result = $joinpoint->result;
			$result2 = $joinpointF_$count($process_arguments);
			return isset($result) ? $result : $result2;
		';
		$joinpoint_code = '
				$joinpoint = new SAF\Framework\Before_Function_Joinpoint(
					"$joinpointF", $parameters_string, $advice_string
				)
			';
		return self::addFunctionCall($joinpoint, $advice, $code, $joinpoint_code);
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @return integer|integer[]
	 */
	public static function addBeforeMethodCall($joinpoint, $advice)
	{
		if (self::DEBUG) echo "before ";
		$code = '
			$result = call_user_func_array($advice_string, array($advice_arguments));
			if (!isset($result)) $result = $joinpoint->result;
			$result2 = $this->_$joinpointM_$count($process_arguments);
			return isset($result) ? $result : $result2;
		';
		$joinpoint_code = '
				$joinpoint = new SAF\Framework\Before_Method_Joinpoint(
					__CLASS__, array($this, "$joinpointM"), $parameters_string, $advice_string
				)
			';
		return self::addMethodCall($joinpoint, $advice, $code, $joinpoint_code);
	}

	//------------------------------------------------------------------------------- addFunctionCall
	/**
	 * @param $joinpoint string the joinpoint defined like a call-back : "functionName"
	 * @param $advice    callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $code           string
	 * @param $joinpoint_code string
	 * @return integer
	 */
	private static function addFunctionCall($joinpoint, $advice, $code, $joinpoint_code)
	{
		$count = count(self::$joinpoints);
		$advice_string = self::callbackString($advice, $count);

		// joinpoint function and arguments
		$function = (new Reflection_Function($joinpoint));
		$arguments = $function->getParameters();
		if ($arguments) {
			$remove = (substr(reset($arguments)->name, 0, 3) == "__");
			$arguments_names = array_keys($arguments);

			// runkit replacement function declaration arguments : all by reference and with $__ names
			$function_arguments = $remove
				? join(", ", $arguments)
				: str_replace('$', '$__', join(", ", $arguments));

			// joinpoint method processing call arguments : all with $__ names
			$process_arguments = $remove
				? ('$' . join(', $', $arguments_names))
				: ('$__' . join(', $__', $arguments_names));

			// the parameters used to initialize the joinpoint
			$parameters_string = 'array(';
			foreach ($arguments_names as $key => $name) {
				if ($remove) {
					$key = substr($key, 2);
					$name = substr($name, 2);
				}
				if ($key) $parameters_string .= ', ';
				$parameters_string .= $key . ' => &$__' . $name . ', "' . $name . '" => &$__' . $name;
			}
			$parameters_string .= ')';

		}
		else {
			$function_arguments = "";
			$process_arguments  = "";
			$parameters_string  = "array()";
			$remove = false;
		}

		// advice arguments are the parameters of the advice method/function.
		// They can be $joinpoint too, then the AOP values of these parameters will override the
		// pointcut values. If they are $result or $object, the AOP values will be send only if
		// the pointcut has no parameters with the same name.
		$advice_method = is_array($advice)
			? new Reflection_Method(is_object($advice[0]) ? get_class($advice[0]) : $advice[0], $advice[1])
			: new Reflection_Function($advice);
		$advice_parameters = $advice_method->getParameters();
		if ($advice_parameters) {
			$advice_arguments = ('&$__' . join(', &$__', array_keys($advice_parameters)));
			if (
				isset($advice_parameters["result"])
				&& !isset($arguments[$remove ? "__result" : "result"])
			) {
				$advice_arguments = str_replace('&$__result', '&$result', $advice_arguments);
			}
			if (
				isset($advice_parameters["object"])
				&& !isset($arguments[$remove ? "__object" : "object"])
			) {
				$advice_arguments = str_replace('&$__object', '&$this', $advice_arguments);
			}
			if (isset($advice_parameters["joinpoint"])) {
				$advice_arguments = str_replace('&$__joinpoint', $joinpoint_code, $advice_arguments);
				$joinpoint_advice_parameter = true;
			}
		}
		else {
			$advice_arguments = "";
		}

		if (!isset($joinpoint_advice_parameter)) {
			$code = str_replace(
				array('if (!isset($result)) $result = $joinpoint->result;', '$joinpoint->result'),
				array('', 'null'),
				$code
			);
		}

		$code = str_replace(
			array(
				'$advice_arguments', '$process_arguments', '$parameters_string', '$advice_string',
				'$joinpointF', '$count'
			),
			array(
				$advice_arguments, $process_arguments, $parameters_string, $advice_string,
				$joinpoint, $count
			),
			$code
		);

		if (!runkit_function_rename($joinpoint, $joinpoint . "_" . $count)) {
			trigger_error("Could not rename $joinpoint to {$joinpoint}_$count", E_USER_ERROR);
		}

		if (self::DEBUG) echo "$joinpoint<br>";
		if (self::DEBUG) echo "<pre>$function_arguments : " . print_r($code, true) . "</pre>";

		// add poincut function
		if (!runkit_function_add($joinpoint, $function_arguments, $code)) {
			trigger_error("Could not add function $joinpoint($process_arguments)", E_USER_ERROR);
		}

		self::$advices[$count] = $advice;
		self::$joinpoints[$count] = $joinpoint;
		return $count;
	}

	//--------------------------------------------------------------------------------- addMethodCall
	/**
	 * @param $joinpoint string[] the joinpoint defined like a call-back :
	 *        array("class_name", "methodName")
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $code           string
	 * @param $joinpoint_code string
	 * @return integer|integer[]
	 */
	private static function addMethodCall($joinpoint, $advice, $code, $joinpoint_code)
	{
		$counts = array();
		$trait = new Reflection_Class($joinpoint[0]);
		if ($trait->isTrait()) {
			foreach ($trait->getDeclaredClassesUsingTrait() as $class) {
				$counts[] = self::addBeforeMethodCall(array($class->name, $joinpoint[1]), $advice);
			}
		}
		$count = count(self::$joinpoints);
		$advice_string = self::callbackString($advice, $count);

		// joinpoint method and arguments
		$method = (new Reflection_Method($joinpoint[0], $joinpoint[1]));
		$arguments = $method->getParameters();
		if ($arguments) {
			$arguments_names = array_keys($arguments);

			// runkit replacement method declaration arguments : all by reference and with $__ names
			$method_arguments = str_replace('$', '$__', join(", ", $arguments));

			// joinpoint method processing call arguments : all with $__ names
			$process_arguments = '$__' . join(', $__', $arguments_names);

			// the parameters used to initialize the joinpoint
			$parameters_string = 'array(';
			foreach ($arguments_names as $key => $name) {
				if ($key) $parameters_string .= ', ';
				$parameters_string .= $key . ' => &$__' . $name . ', "' . $name . '" => &$__' . $name;
			}
			$parameters_string .= ')';

		}
		else {
			$method_arguments  = "";
			$process_arguments = "";
			$parameters_string = "array()";
		}

		// advice arguments are the parameters of the advice method/function.
		// They can be $joinpoint too, then the AOP values of these parameters will override the
		// pointcut values. If they are $result or $object, the AOP values will be send only if
		// the pointcut has no parameters with the same name.
		$advice_method = is_array($advice)
			? new Reflection_Method(
				is_object($advice[0]) ? get_class($advice[0]) : $advice[0], $advice[1]
			)
			: new Reflection_Function($advice);
		$advice_parameters = $advice_method->getParameters();
		if ($advice_parameters) {
			$advice_arguments = ('&$__' . join(', &$__', array_keys($advice_parameters)));
			if (isset($advice_parameters["result"]) && !isset($arguments["result"])) {
				$advice_arguments = str_replace('&$__result', '&$result', $advice_arguments);
			}
			if (isset($advice_parameters["object"]) && !isset($arguments["object"])) {
				$advice_arguments = str_replace('&$__object', '&$this', $advice_arguments);
			}
			if (isset($advice_parameters["joinpoint"])) {
				$advice_arguments = str_replace('&$__joinpoint', $joinpoint_code, $advice_arguments);
				$joinpoint_advice_parameter = true;
			}
		}
		else {
			$advice_arguments = "";
		}

		if (!isset($joinpoint_advice_parameter)) {
			$code = str_replace(
				array('if (!isset($result)) $result = $joinpoint->result;', '$joinpoint->result'),
				array('', 'null'),
				$code
			);
		}

		$code = str_replace(
			array(
				'$advice_arguments', '$process_arguments', '$parameters_string', '$advice_string',
				'$joinpointM', '$count', '__CLASS__'
			),
			array(
				$advice_arguments, $process_arguments, $parameters_string, $advice_string,
				$joinpoint[1], $count, "'$joinpoint[0]'"
			),
			$code
		);

		if (!runkit_method_rename($joinpoint[0], $joinpoint[1], "_" . $joinpoint[1] . "_" . $count)) {
			trigger_error(
				"Could not rename $joinpoint[0]::$joinpoint[1] to _$joinpoint[1]_$count", E_USER_ERROR
			);
		}

		$acc = 0;
		if ($method->isPublic())    $acc |= RUNKIT_ACC_PUBLIC;
		if ($method->isProtected()) $acc |= RUNKIT_ACC_PROTECTED;
		if ($method->isPrivate())   $acc |= RUNKIT_ACC_PRIVATE;
		if ($method->isStatic()) {
			$acc |= RUNKIT_ACC_STATIC;
			$code = str_replace(
				array('$this->', '$this'),
				array('self::', "get_called_class()"),
				$code
			);
		}

		if (self::DEBUG) echo "$joinpoint[0]::$joinpoint[1]<br>";
		if (self::DEBUG) echo "<pre>$method_arguments : " . print_r($code, true) . "</pre>";

		// add poincut method
		if (!runkit_method_add($joinpoint[0], $joinpoint[1], $method_arguments, $code, $acc)) {
			trigger_error(
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
	 * @param $advice callable the call-back call of the advice :
	 *        array("class_name", "methodName"), array($object, "methodName"), "functionName"
	 * @param $side      string Aop::READ or Aop::WRITE
	 */
	private static function addOnProperty($joinpoint, $advice, $side)
	{
		list($class_name, $property_name) = $joinpoint;
		if (!isset(Aop::$properties[$class_name])) {
			$aop_properties = __CLASS__ . "::\$properties['$class_name']";
			if (!(
				method_exists($class_name, '__aop')
				&& ((new Reflection_Method($class_name, '__aop'))->class == $class_name)
			)) {
				// magic method __aop : initializes aop, must be called on beginning of __construct
				if (!runkit_method_add($class_name, '__aop', '', '
					foreach (array_keys(' . $aop_properties . ') as $property_name) {
						$_property_name = "_" . $property_name;
						$this->$_property_name = $this->$property_name;
						unset($this->$property_name);
					}
				')) {
					trigger_error("Could not add method $class_name::__aop", E_USER_ERROR);
				}
				// set magic methods for AOP
				$replaced = self::rename($class_name, '__get');
				runkit_method_add($class_name, '__get', '$property', '
					if ($property[0] == "_") {
						return ' . ($replaced ? '$this->__get_aop($property)' : 'null') . ';
					}
					$_property = "_" . $property;
					$value = isset($this->$_property) ? $this->$_property : null;
					if (isset(' . $aop_properties . '[$property]["read"])) {
						$joinpoint = new SAF\Framework\Property_Read_Joinpoint(
							"' . $joinpoint[0] . '", $this, $property
						);
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
				$replaced = self::rename($class_name, '__isset');
				runkit_method_add($class_name, '__isset', '$property', '
					if ($property[0] == "_") {
						return ' . ($replaced ? '$this->__isset_aop($property)' : 'false') . ';
					}
					$_property = "_" . $property;
					return isset($this->$_property);
				');
				$replaced = self::rename($class_name, '__set');
				runkit_method_add($class_name, '__set', '$property, $value', '
					if ($property[0] == "_") {
						' . ($replaced ? '$this->__set_aop($property, $value)' : '$this->$property = $value') . ';
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
				$replaced = self::rename($class_name, '__unset');
				runkit_method_add($class_name, '__unset', '$property', '
					if ($property[0] != "_") {
						$property = "_" . $property;
						unset($this->$property);
					}
					' . ($replaced ? 'else $this->__unset_aop($property);' : '') . '
				');
				// currently existing constructor renamed as __construct_aop (if exists)
				if (
					method_exists($class_name, '__construct')
					&& ((new Reflection_Method($class_name, '__construct'))->class == $class_name)
				) {
					$__construct = new Reflection_Method($class_name, '__construct');
					$arguments = $__construct->getParameters();
					$method_arguments = join(", ", $arguments);
					$process_arguments = ($arguments ? '$' . join(', $', array_keys($arguments)) : '');
					if ($class_name == $__construct->class) {
						runkit_method_rename($class_name, '__construct', '__construct_aop');
						$construct_call = "\nself::__construct_aop($process_arguments);\n";
					}
					else {
						$construct_call = "\nparent::__construct($process_arguments);\n";
					}
				}
				else {
					$method_arguments = "";
					$construct_call = "";
				}
				$code = "self::__aop();" . $construct_call;
				runkit_method_add($class_name, '__construct', $method_arguments, $code);
			}
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
	 * @param $advice callable the call-back call of the advice :
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
	 * @param $advice callable the call-back call of the advice :
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
	public function registerProperties($class_name, $annotation, $function)
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
								$call_class  = $this;
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
			//echo "- DEAD CODE : Register properties for non existing class $class_name : $annotation<br>";
		}
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an AOP link, knowing its handler returned when calling the add* methods
	 *
	 * @param $handler integer|integer[]
	 * @todo Works only with the last added advice on a joinpoint : do not remove a "middle" advice !
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
					runkit_function_rename($joinpoint . "_" . $handler, $joinpoint);
				}
				self::$joinpoints[$handler] = null;
			}
		}
	}

	//---------------------------------------------------------------------------------------- rename
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @return boolean
	 */
	private static function rename($class_name, $method_name)
	{
		if (
			method_exists($class_name, $method_name)
			&& ((new Reflection_Method($class_name, $method_name))->class == $class_name)
		) {
			runkit_method_rename($class_name, $method_name, $method_name . '_aop');
			return true;
		}
		return false;
	}

}
