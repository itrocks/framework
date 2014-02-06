<?php
namespace SAF\AOP;

use SAF\Framework\Reflection_Function;
use SAF\Framework\Reflection_Method;
use SAF\Plugins\Plugin;

/**
 * Functions common to all element compilers classes
 */
trait Compiler_Toolbox
{

	//---------------------------------------------------------------------------------- decodeAdvice
	/**
	 * @param $advice               string
	 * @param $joinpoint_class_name string
	 * @return array
	 */
	private function decodeAdvice($advice, $joinpoint_class_name)
	{
		if (is_array($advice)) {
			$advice_function_name = null;
			list($advice_object, $advice_method_name) = $advice;
			if (is_object($advice_object)) {
				$advice_class_name = get_class($advice_object);
				$is_advice_static = false;
				if ($advice_object instanceof Plugin) {
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
				$advice_class_name = (in_array($advice_object, array('self', '$this')))
					? $joinpoint_class_name
					: $advice_class_name = $advice_object;
				if ($advice_object == '$this') {
					$advice_string = 'array($this, \'' . $advice_method_name . '\')';
					$is_advice_static = false;
				}
				else {
					$advice_string = "array('" . $advice_class_name . "', '" . $advice_method_name . "')";
					$is_advice_static = true;
				}
			}
			$advice_method = new Reflection_Method($advice_class_name, $advice_method_name);
		}
		else {
			$advice_class_name = null;
			$advice_method_name = null;
			$advice_function_name = $advice;
			$advice_string = "'" . $advice_function_name . "'";
			$is_advice_static = false;
			$advice_method = new Reflection_Function($advice_function_name);
		}

		$advice_has_return = strpos($advice_method->getDocComment(), '@return');
		$advice_parameters = $advice_method->getParameters();

		return array(
			$advice_class_name,
			$advice_method_name,
			$advice_function_name,
			$advice_parameters,
			$advice_string,
			$advice_has_return,
			$is_advice_static
		);
	}

	//---------------------------------------------------------------------------- generateAdviceCode
	/**
	 * @param $advice                   string[]|object[]|string
	 * @param $advice_class_name        string
	 * @param $advice_method_name       string
	 * @param $advice_function_name     string
	 * @param $advice_parameters_string string
	 * @param $advice_has_return        boolean
	 * @param $is_advice_static         boolean
	 * @param $joinpoint_code           string
	 * @param $i2                       string
	 * @param $result                   string
	 * @return string
	 */
	private function generateAdviceCode(
		$advice, $advice_class_name, $advice_method_name, $advice_function_name,
		$advice_parameters_string, $advice_has_return, $is_advice_static, $joinpoint_code,
		$i2, $result
	) {
		// $advice_code
		if (is_array($advice)) {
			// static method call
			if ($is_advice_static) {
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . ' = ') : '')
					. (($advice[0] == 'self') ? 'self' : ('\\' . $advice_class_name))
					. '::' . $advice_method_name
					. '(' . $advice_parameters_string . ');';
			}
			// object method call
			elseif ($advice[0] == '$this') {
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . ' = ') : '')
					. '$this->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			}
			else {
				return $i2 . '/** @var $object_ \\' . $advice_class_name . ' */'
					. $i2 . '$object_ = \\SAF\\Framework\\Session::current()->plugins->get('
						. "'$advice_class_name'"
					. ');'
					. $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . ' = ') : '')
					. '$object_->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			}
		}
		// function call
		else {
			return $joinpoint_code
				. $i2 . ($advice_has_return ? $result . ' = ' : '')
				. $advice_function_name . '(' . $advice_parameters_string . ');';
		}
	}

}
