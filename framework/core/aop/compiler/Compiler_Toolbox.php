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
	 * @param $advice string
	 * @return array
	 */
	private function decodeAdvice($advice)
	{
		if (is_array($advice)) {
			$advice_function_name = null;
			list($advice_object, $advice_method_name) = $advice;
			$advice_method = new Reflection_Method(
				is_object($advice_object) ? get_class($advice_object) : $advice_object,
				$advice_method_name
			);
			if (is_object($advice_object)) {
				$is_advice_static = false;
				$advice_class_name = get_class($advice_object);
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
				$advice_class_name = $advice_object;
				$advice_string = "array('" . $advice_class_name . "', '" . $advice_method_name . "')";
				$is_advice_static = true;
			}
		}
		else {
			$advice_class_name = null;
			$advice_method_name = null;
			$advice_function_name = $advice;
			$advice_method = new Reflection_Function($advice_function_name);
			$advice_string = "'" . $advice_function_name . "'";
			$is_advice_static = false;
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
				$advice_code = $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . ' = ') : '')
					. $advice_class_name . '::' . $advice_method_name
					. '(' . $advice_parameters_string . ');';
				return $advice_code;
			}
			// object method call
			else {
				$advice_code = $i2 . '/** @var $object_ ' . "\\" . $advice_class_name . ' */'
					. $i2 . '$object_ = Session::current()->plugins->get(' . "'$advice_class_name'" . ');'
					. $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . ' = ') : '')
					. '$object_->' . $advice_method_name . '(' . $advice_parameters_string . ');';
				return $advice_code;
			}
		}
		// function call
		else {
			$advice_code = $joinpoint_code
				. $i2 . ($advice_has_return ? $result . ' = ' : '')
				. $advice_function_name . '(' . $advice_parameters_string . ');';
			return $advice_code;
		}
	}

}
