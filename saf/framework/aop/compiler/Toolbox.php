<?php
namespace SAF\Framework\AOP\Compiler;

use ReflectionFunction;
use SAF\Framework\PHP\Reflection_Method;
use SAF\Framework\Plugin;
use SAF\Framework\Reflection\Reflection_Function;

/**
 * Functions common to all element compilers classes
 */
trait Toolbox
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
					$advice_string = '[$object_, ' . Q . $advice_method_name . Q . ']';
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
				$advice_class_name = (in_array($advice_object, ['self', '$this']))
					? $joinpoint_class_name
					: $advice_class_name = $advice_object;
				if ($advice_object == '$this') {
					$advice_string = '[$this, ' . Q . $advice_method_name . Q . ']';
					$is_advice_static = false;
				}
				else {
					$advice_string = '[' . Q . $advice_class_name . Q . ',' . SP . Q . $advice_method_name . Q . ']';
					$is_advice_static = true;
				}
			}
			$advice_method = Reflection_Method::of(
				$advice_class_name, $advice_method_name, [T_EXTENDS, T_USE]
			);
			$advice_parameters = $advice_method->getParametersNames();
		}
		else {
			$advice_class_name = null;
			$advice_method_name = null;
			$advice_function_name = $advice;
			$advice_string = Q . $advice_function_name . Q;
			$is_advice_static = false;
			$advice_method = new Reflection_Function($advice_function_name);
			$advice_parameters = $advice_method->getParameters();
		}

		$advice_has_return = (strpos($advice_method->getDocComment(), '@return') !== false);

		return [
			$advice_class_name,
			$advice_method_name,
			$advice_function_name,
			$advice_parameters,
			$advice_string,
			$advice_has_return,
			$is_advice_static
		];
	}

	//--------------------------------------------------------------------------------- displayAdvice
	/**
	 * @param $advice array
	 * @return string
	 */
	private function displayAdvice($advice)
	{
		$advice = $advice[1];
		if (is_string($advice)) {
			return $advice . '()';
		}
		if (is_object($advice[0])) {
			return '$(' . get_class($advice[0]) . ')->' . $advice[1] . '()';
		}
		else {
			return $advice[0] . '::' . $advice[1] . '()';
		}
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
			$method = Reflection_Method::of($advice_class_name, $advice_method_name, [T_EXTENDS, T_USE]);
			$ref = $method->returnsReference() ? '&' : '';
			// static method call
			if ($is_advice_static) {
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . SP . '=' . $ref . SP) : '')
					. (($advice[0] == 'self') ? 'self' : (BS . $advice_class_name))
					. '::' . $advice_method_name
					. '(' . $advice_parameters_string . ');';
			}
			// object method call
			elseif ($advice[0] == '$this') {
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . SP . '=' . $ref . SP) : '')
					. '$this->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			}
			else {
				return $i2 . '/** @var $object_ ' . BS . $advice_class_name . ' */'
					. $i2 . '$object_ = \SAF\Framework\Session::current()->plugins->get('
						. "'$advice_class_name'"
					. ');'
					. $joinpoint_code
					. $i2 . ($advice_has_return ? ('if ($object_) ' . $result . SP . '=' . $ref . SP) : '')
					. '$object_->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			}
		}
		// function call
		else {
			$ref = (new ReflectionFunction($advice_function_name))->returnsReference() ? '&' : '';
			return $joinpoint_code
				. $i2 . ($advice_has_return ? $result . SP . '=' . $ref . SP : '')
				. $advice_function_name . '(' . $advice_parameters_string . ');';
		}
	}

}
