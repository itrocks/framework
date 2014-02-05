<?php
namespace SAF\AOP;

use SAF\Framework\Reflection_Class;
use SAF\Framework\Reflection_Function;
use SAF\Framework\Reflection_Method;
use SAF\Plugins;

/**
 * Standard aspect weaver compiler
 */
class Compiler implements ICompiler
{

	const DEBUG = true;

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $buffer string
	 */
	private function cleanup(&$buffer)
	{
		// remove all "\r"
		$buffer = trim(str_replace("\r", '', $buffer));
		// remove since the line containing "//#### AOP" until the end of the file
		$expr = '`\n\s*//#+\s+AOP.*}([\s*\n]*\})[\s*\n]*`s';
		$buffer = preg_replace($expr, '$1', $buffer);
		// replace "/* public */ private [static] function name_(" by "public [static] function name("
		$expr = '`(\n\s*)/\*\s*(private|protected|public)\s*\*/(\s*)((private|protected|public)\s*)?'
			. '(static\s*)?function(\s+\w*)\_\s*\(`';
		$buffer = preg_replace($expr, '$1$2$3$6function$7(', $buffer);
	}

	//---------------------------------------------------------------------------------- codeAssembly
	/**
	 * @param $before_code string[]
	 * @param $advice_code string
	 * @param $after_code  string[]
	 * @return string
	 */
	private function codeAssembly($before_code, $advice_code, $after_code)
	{
		return trim(
			($before_code ? "\n" : "") . join("\n", array_reverse($before_code))
			. "\n" . $advice_code
			. ($after_code ? "\n" : "") . join("\n", $after_code)
		);
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $weaver IWeaver
	 */
	public function compile(IWeaver $weaver)
	{
		$start_time = microtime(true);
		if (!($weaver instanceof Weaver)) {
			trigger_error('Compiler can only compile aspect weaver of class Weaver', E_USER_ERROR);
			return;
		}
		foreach ($weaver->getJoinpoints() as $joinpoint => $pointcuts) {
			if (ctype_lower($joinpoint)) {
				$this->willCompileFunction($joinpoint, $pointcuts);
			}
			else {
				$class_name = $joinpoint;
				$methods    = array();
				$properties = array();
				foreach ($pointcuts as $joinpoint2 => $pointcuts2) {
					foreach ($pointcuts2 as $pointcut) {
						if (($pointcut[0] == 'read') || ($pointcut[0] == 'write')) {
							$properties[$joinpoint2] = $pointcuts2;
						}
						else {
							$methods[$joinpoint2] = $pointcuts2;
						}
					}
				}
				$this->compileClass($class_name, $methods, $properties);
			}
		}
		if (self::DEBUG) echo "duration = " . (microtime(true) - $start_time) . "<br>";
	}

	//---------------------------------------------------------------------------------- compileClass
	/**
	 * @param $class_name string
	 * @param $methods    array
	 * @param $properties array
	 */
	private function compileClass($class_name, $methods, $properties)
	{
		$file_name = (new Reflection_Class($class_name))->getFileName();
		/** @noinspection PhpIncludeInspection */
		include_once $file_name;
		$buffer = file_get_contents($file_name);
		$this->cleanup($buffer);
		$buffer = substr($buffer, 0, -1) . "\t//" . str_repeat('#', 91) . " AOP\n";

		if (self::DEBUG) echo "<h2>compile class $class_name</h2>";

		foreach ($methods as $method_name => $advices) {
			$this->compileMethod($class_name, $method_name, $advices, $buffer);
		}

		$buffer .= "\n}";

		file_put_contents($file_name, $buffer);

		if (self::DEBUG) echo "<pre>properties = " . print_r($properties, true) . "</pre>";
		if (self::DEBUG) echo "<pre>" . htmlentities($buffer) . "</pre>";
	}

	//--------------------------------------------------------------------------------- compileMethod
	/**
	 * @param $class_name  string
	 * @param $method_name string
	 * @param $advices     array
	 * @param $buffer      string
	 */
	public function compileMethod($class_name, $method_name, $advices, &$buffer)
	{
		if (self::DEBUG) echo "<h3>$class_name::$method_name</h3>";

		$append = "";

		$in_parent = false;
		$source_method = new Reflection_Method(
			$class_name,
			method_exists($class_name, $method_name . '_') ? ($method_name . '_') : $method_name
		);
		$preg_expr = '`(\n\s*)((private|protected|public)\s*)?((static\s*)?function\s+)'
			. '(' . $method_name . ')(\s*\()`';
		// 0 : "\n\tpublic static function methodName ("
		// 1 : "\n\t"
		// 2 : "public "
		// 3 : "public"
		// 4 : "static function "
		// 5 : "static "
		// 6 : "methodName"
		// 7 : " ("
		preg_match($preg_expr, $buffer, $match);
		if (!$match) {
			if (!method_exists($class_name, $method_name)) {
				user_error("$class_name::$method_name not found", E_USER_ERROR);
			}
			else {
				$in_parent = true;
				$source_method_buffer = file_get_contents($source_method->getFileName());
				preg_match($preg_expr, $source_method_buffer, $match);
				if (!$match) {
					if (self::DEBUG) echo "<pre>" . htmlentities($buffer) . "</pre>";
					user_error(
						"$class_name::$method_name not found into " . $source_method->class, E_USER_ERROR
					);
				}
			}
		}
		if (self::DEBUG) echo "<pre>" . htmlentities($preg_expr) . "</pre>";
		if (self::DEBUG) echo "<pre>" . print_r($match, true) . "</pre>";
		// $indent = prototype level indentation spaces
		$indent = $match[1];
		$i2 = $indent . "\t";
		$i3 = $i2 . "\t";
		// $parameters = array('parameter_name' => Reflection_Parameter)
		$parameters = $source_method->getParameters();
		// $doc_comment = source method doc comment
		$doc_comment = $source_method->getDocComment();
		// $parameters_names = '$parameter1, $parameter2'
		$parameters_names = $parameters ? ('$' . join(', $', array_keys($parameters))) : '';
		$parameters_string = join(', ', $parameters);
		// $prototype = 'public [static] function methodName($parameter1, $parameter2 = "default")'
		$prototype = $match[0] . $parameters_string . ')' . $indent . '{' . $i2;
		/** $is_static = '[static]' */
		$is_static = trim($match[5]);
		/** @var $count integer around method counter */
		$count = null;

		// $joinpoint_has_return
		$joinpoint_has_return = strpos($doc_comment, '@return');

		// $pointcut_string
		if ($is_static) {
			$pointcut_string = "array(get_called_class(), '$method_name')";
		}
		else {
			$pointcut_string = 'array($this, ' . "'$method_name'" . ')';
		}

		// $code the generated code starts by the doc comments and prototype
		$after_code  = array();
		$before_code = array();
		$advices_count = count($advices);
		$advice_number = 0;

		$call_code = $i2 . '$result_ = '
			. ($is_static ? 'self::' : ($in_parent ? 'parent::' : '$this->'))
			. $method_name . (!$in_parent ? ('_' . $count) : '')
			. '(' . $parameters_names . ');';

		foreach (array_reverse($advices) as $advice) {
			$advice_number++;
			$type = $advice[0];

			if (self::DEBUG) echo "<h4>$type => " . print_r($advice[1], true) . "</h4>";

			// $advice_object, $advice_method_name, $advice_method, $is_advice_static
			// $advice_string = "array($object_, 'methodName')" | "'functionName'"
			if (is_array($advice)) {
				$advice_function_name = null;
				list($advice_object, $advice_method_name) = $advice[1];
				$advice_method = new Reflection_Method(
					is_object($advice_object) ? get_class($advice_object) : $advice_object,
					$advice_method_name
				);
				if (is_object($advice_object)) {
					$is_advice_static = false;
					$advice_class_name = get_class($advice_object);
					if ($advice_object instanceof Plugins\Plugin) {
						$advice_string = 'array($object_, ' . "'" . $advice_method_name . "'" . ')';
					}
					else {
						trigger_error(
							'Compiler does not how to compile non-plugin objects (' . $advice_class_name . ')',
							E_USER_ERROR
						);
						$advice_string = null;
					}
				}
				else {
					$advice_class_name = $advice_object;
					$advice_string = "array('" . $advice_class_name . "', '" . $advice_method_name . "')";
					$is_advice_static = true;
				}
			}
			else {
				$advice_class_name = null;
				$advice_method_name = null;
				$advice_function_name = $advice[1];
				$advice_method = new Reflection_Function($advice_function_name);
				$advice_string = "'" . $advice_function_name . "'";
				$is_advice_static = false;
			}

			// $advice_has_return, $advice_parameters, $advice_parameters_names, $joinpoint_code
			$advice_has_return = strpos($advice_method->getDocComment(), '@return');
			$advice_parameters = $advice_method->getParameters();
			$joinpoint_code = '';
			if ($advice_parameters) {
				$advice_parameters_string = '$' . join(', $', array_keys($advice_parameters));
				if (isset($advice_parameters['result']) && !isset($parameters['result'])) {
					$advice_parameters_string = str_replace('$result', '$result_', $advice_parameters_string);
				}
				if (isset($advice_parameters['object']) && !isset($parameters['object'])) {
					$advice_parameters_string = str_replace('$object', '$this', $advice_parameters_string);
				}
				if (isset($advice_parameters['joinpoint'])) {
					$advice_parameters_string = str_replace(
						'$joinpoint', '$joinpoint_', $advice_parameters_string
					);
					//$joinpoint_parameters_string = 'array(' . str_replace('$', '&$', $parameters_names) . ')';
					$joinpoint_parameters_string = 'array(';
					foreach (array_keys($parameters) as $key => $name) {
						if ($key) $joinpoint_parameters_string .= ', ';
						$joinpoint_parameters_string .= $key . ' => &$' . $name . ', "' . $name . '" => &$__' . $name;
					}
					$joinpoint_parameters_string .= ')';
					switch ($type) {
						case 'after':
							$joinpoint_code = $i2 . '$joinpoint_ = new SAF\Framework\After_Method_Joinpoint('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string . ', $result_, ' . $advice_string
								. $i2 . ');';
							break;
						case 'around':
							$process_callback = $method_name . '_' . $count;
							$joinpoint_code = $i2 . '$joinpoint_ = new SAF\Framework\Around_Method_Joinpoint('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string . ', ' . $advice_string . ', ' . "'$process_callback'"
								. $i2 . ');';
							break;
						case 'before':
							$joinpoint_code = $i2 . '$joinpoint_ = new SAF\Framework\Before_Method_Joinpoint('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string . ', ' . $advice_string
								. $i2 . ');';
							break;
					}
				}
			}
			else {
				$advice_parameters_string = '';
			}

			// $advice_code
			if (is_array($advice)) {
				// static method call
				if ($is_advice_static) {
					$advice_code = $joinpoint_code . $i2 . $advice_class_name . '::' . $advice_method_name
						. '(' . $advice_parameters_string . ');';
				}
				// object method call
				else {
					$advice_code = $i2 . '/** @var $object_ ' . $advice_class_name . ' */'
						. $i2 . '$object_ = Session::current()->plugins->get(' . "'$advice_class_name'" . ');'
						. $joinpoint_code
						. $i2 . ($advice_has_return ? '$result_ = ' : '')
						. '$object_->' . $advice_method_name . '(' . $advice_parameters_string . ');';
				}
			}
			// function call
			else {
				$advice_code = $joinpoint_code . '
					' . $advice_function_name . '(' . $advice_parameters_string . ');';
			}

			switch ($type) {
				case 'after':
					if ($advice_has_return) {
						$advice_code = str_replace('$result_ = ', '$result2_ = ', $advice_code);
						$advice_code .= $i2 . 'if (isset($result2_)) $result_ = $result2_;';
					}
					if ($joinpoint_code) {
						$advice_code .= $i2 . 'if ($joinpoint_->stop) return $result_;';
					}
					$after_code[] = $advice_code;
					break;
				case 'around':
					$my_prototype = ($advice_number == $advices_count)
						? $prototype
						: str_replace($method_name, $method_name . '_' . $count , $prototype);
					$append .= $my_prototype
						. $this->codeAssembly($before_code, $advice_code, $after_code)
						. ($joinpoint_has_return ? ("\n" . $i2 . 'return $result_;') : '')
						. $indent . "}\n";
					$count ++;
					$before_code = array();
					$after_code = array();
					break;
				case 'before':
					if ($advice_has_return) {
						$advice_code .= $i2 . 'if (isset($result_)) return $result_;';
					}
					if ($joinpoint_code) {
						$advice_code .= $i2 . 'if ($joinpoint_->stop)) return $result_;';
					}
					$before_code[] = $advice_code;
					break;
			}
		}
		if ($before_code || $after_code) {
			$append .= $indent . $doc_comment . $prototype
				. $this->codeAssembly($before_code, $call_code, $after_code)
				. ($joinpoint_has_return ? ("\n" . $i2 . 'return $result_;') : '')
				. $indent . "}\n";
		}

		$buffer = preg_replace(
			$preg_expr, $indent . '/* $2*/ private $4' . $method_name . '_' . $count . '$7', $buffer
		) . $append;
	}

	//--------------------------------------------------------------------------- willCompileFunction
	private function willCompileFunction()
	{
		trigger_error('Compiler does not know how to compile function joinpoints', E_USER_ERROR);
	}

}
