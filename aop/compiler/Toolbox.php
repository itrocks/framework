<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Method;
use ITRocks\Framework\Plugin;
use ITRocks\Framework\Reflection\Reflection_Function;
use ReflectionFunction;

/**
 * Functions common to all element compilers classes
 */
trait Toolbox
{

	//---------------------------------------------------------------------------------------- $class
	private Reflection_Class $class;

	//---------------------------------------------------------------------------------- decodeAdvice
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $advice object[]|string|string[]
	 */
	private function decodeAdvice(array|string $advice, string $joinpoint_class_name) : array
	{
		if (is_array($advice)) {
			$advice_function_name = null;
			[$advice_object, $advice_method_name] = $advice;
			if (is_object($advice_object)) {
				$advice_class_name = get_class($advice_object);
				$is_advice_static  = false;
				if ($advice_object instanceof Plugin) {
					$advice_string = '[$object_, ' . Q . $advice_method_name . Q . ']';
				}
				else {
					trigger_error(
						'Compiler does not how to compile non-plugin objects (' . $advice_class_name . ')',
						E_USER_ERROR
					);
				}
			}
			else {
				$advice_class_name = (in_array($advice_object, ['self', 'static', '$this']))
					? $joinpoint_class_name
					: $advice_object;
				if ($advice_object === '$this') {
					$advice_string    = '[$this, ' . Q . $advice_method_name . Q . ']';
					$is_advice_static = false;
				}
				else {
					$advice_string = '['
						. Q . $advice_class_name . Q . ',' . SP . Q . $advice_method_name . Q
						. ']';
					$is_advice_static = true;
				}
			}
			$methods_flags = [Reflection_Class::T_DOC_EXTENDS, T_EXTENDS, T_USE];
			$advice_method = ($advice_object === '$this')
				? $this->class->getMethods($methods_flags)[$advice_method_name]
				: Reflection_Method::of($advice_class_name, $advice_method_name, $methods_flags);
			$advice_parameters = $advice_method->getParametersNames();
		}
		else {
			$advice_class_name    = null;
			$advice_method_name   = null;
			$advice_function_name = $advice;
			$advice_string        = Q . $advice_function_name . Q;
			$is_advice_static     = false;
			/** @noinspection PhpUnhandledExceptionInspection Only valid advices are allowed */
			$advice_method     = new Reflection_Function($advice_function_name);
			$advice_parameters = $advice_method->getParameters();
		}

		$advice_has_return = $advice_method->returns();

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
	private function displayAdvice(array $advice) : string
	{
		$advice = $advice[1];
		if (is_string($advice)) {
			return $advice . '()';
		}
		return is_object($advice[0])
			? ('$(' . get_class($advice[0]) . ')->' . $advice[1] . '()')
			: ($advice[0] . '::' . $advice[1] . '()');
	}

	//---------------------------------------------------------------------------- generateAdviceCode
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $advice string[]|object[]|string
	 */
	private function generateAdviceCode(
		array|string $advice, ?string $advice_class_name, ?string $advice_method_name,
		?string $advice_function_name, string $advice_parameters_string, bool $advice_has_return,
		bool $is_advice_static, string $joinpoint_code, string $i2, string $result,
		string $call_if_no_plugin_object = ''
	) : string
	{
		// $advice_code
		if (is_array($advice)) {
			$methods_flags = [Reflection_Class::T_DOC_EXTENDS, T_EXTENDS, T_USE];
			$method        = ($advice[0] === '$this')
				? $this->class->getMethods($methods_flags)[$advice_method_name]
				: Reflection_Method::of($advice_class_name, $advice_method_name, $methods_flags);
			$ref = $method->returnsReference() ? '&' : '';
			// static method call
			if ($is_advice_static) {
				$access_code = $method->isStatic()
					? (
						(in_array($advice[0], ['self', 'static']) ? $advice[0] : (BS . $advice_class_name))
						. '::'
					)
					: '$this->';
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . SP . '=' . $ref . SP) : '')
					. $access_code . $advice_method_name
					. '(' . $advice_parameters_string . ');';
			}
			// object method call
			elseif ($advice[0] === '$this') {
				return $joinpoint_code
					. $i2 . ($advice_has_return ? ($result . SP . '=' . $ref . SP) : '')
					. '$this->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			}
			$code = $i2 . '$object_ = \ITRocks\Framework\Session::current()->plugins->get('
					. "'$advice_class_name'"
				. ');'
				. $joinpoint_code
				. $i2 . 'if ($object_) ' . ($advice_has_return ? ($result . SP . '=' . $ref . SP) : '')
				. '$object_->' . $advice_method_name . '(' . $advice_parameters_string . ');';
			if ($call_if_no_plugin_object) {
				$code .= $i2 . 'else ' . trim($call_if_no_plugin_object);
			}
			return $code;
		}
		// function call
		/** @noinspection PhpUnhandledExceptionInspection Only valid advices are allowed */
		$ref = (new ReflectionFunction($advice_function_name))->returnsReference() ? '&' : '';
		return $joinpoint_code
			. $i2 . ($advice_has_return ? $result . SP . '=' . $ref . SP : '')
			. $advice_function_name . '(' . $advice_parameters_string . ');';
	}

}
