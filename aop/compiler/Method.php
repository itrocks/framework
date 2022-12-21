<?php
namespace ITRocks\Framework\AOP\Compiler;

use ITRocks\Framework\PHP\Reflection_Class;
use ITRocks\Framework\PHP\Reflection_Method;

/**
 * Aspect weaver method compiler
 */
class Method
{
	use Toolbox;

	//----------------------------------------------------------------------------------------- DEBUG
	const DEBUG = false;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class Reflection_Class
	 */
	public function __construct(Reflection_Class $class)
	{
		$this->class = $class;
	}

	//---------------------------------------------------------------------------------- codeAssembly
	/**
	 * @param $before_code string[]
	 * @param $advice_code string
	 * @param $after_code  string[]
	 * @param $indent      string
	 * @return string
	 */
	private function codeAssembly(
		array $before_code, string $advice_code, array $after_code, string $indent
	) : string
	{
		return trim(
			($before_code ? $indent : '') . join(LF, array_reverse($before_code))
			. $indent . $advice_code
			. ($after_code ? $indent : '') . join(LF, $after_code)
		);
	}

	//--------------------------------------------------------------------------------------- compile
	/**
	 * @param $method_name string
	 * @param $advices     array
	 * @return string
	 */
	public function compile(string $method_name, array $advices) : string
	{
		$methods = $this->class->getMethods([T_EXTENDS, T_IMPLEMENTS, T_USE]);
		if (!isset($methods[$method_name])) {
			trigger_error(
				'AOP Compiler : Method does not exist ' . $this->class->name . '::' . $method_name . '()'
				. ' for advice ' . $this->displayAdvice(reset($advices)),
				E_USER_ERROR
			);
			/** @noinspection PhpUnreachableStatementInspection in case of caught error */
			return '';
		}
		$source_method = $methods[$method_name];
		if (!$source_method) {
			trigger_error($this->class->name . '::' . $method_name . ' not found', E_USER_ERROR);
			/** @noinspection PhpUnreachableStatementInspection in case of caught error */
			return '';
		}
		// don't compile abstract method where they are declared : will be compiled where they are
		// implemented
		if ($source_method->isAbstract()) {
			return '';
		}

		$class_name = $this->class->name;
		$buffer     = $this->class->source->getSource();
		$result     = '';

		if (self::DEBUG) echo '<h3>Method ' . $class_name . '::' . $method_name . '</h3>';

		$in_parent = !$this->class->implementsMethod($source_method->name);
		// preg expression to search and replace things into method prototype
		$preg_expr = Reflection_Method::regex($method_name);
		// $indent = prototype level indentation spaces
		$indent = ($source_method instanceof Reflection_Method)
			? $source_method->getIndent()
			: (LF . TAB);
		$i2 = $indent . TAB;
		$i3 = $i2 . TAB;
		// $parameters = ['parameter_name' => 'parameter_name')
		$parameters = $source_method->getParametersNames();
		// $doc_comment = source method doc comment
		$doc_comment = $source_method->getDocComment();
		// $parameters_names = '$parameter1, $parameter2'
		$parameters_names = $parameters ? ('$' . join(', $', $parameters)) : '';
		// $prototype = 'public [static] function methodName($parameter1, $parameter2 = 'default')'
		$prototype = $source_method->getPrototypeString();
		/** $is_static = '[static]' */
		$is_static = $source_method->isStatic();
		/** @var $count integer around method counter */
		$count = null;

		// $joinpoint_has_return
		$joinpoint_has_return = str_contains($doc_comment, '@return');

		// $pointcut_string
		if ($is_static) {
			$pointcut_string = '[static::class, ' . Q . $method_name . Q . ']';
		}
		else {
			$pointcut_string = '[$this, ' . Q . $method_name . Q . ']';
		}

		// $code the generated code starts by the doc comments and prototype
		$after_code    = [];
		$before_code   = [];
		$advices_count = count($advices);
		$advice_number = 0;

		if (self::DEBUG && $in_parent) {
			echo 'in_parent = true for ' . $class_name . '::' . $method_name . BR;
		}

		$ref       = $source_method->returnsReference() ? '&' : '';
		$call_code = $i2 . ($joinpoint_has_return ? ('$result_ =' . $ref . SP) : '')
			. ($is_static ? 'self::' : ($in_parent ? 'parent::' : '$this->'))
			. $method_name . ($in_parent ? '' : ('_' . $count))
			. '(' . $parameters_names . ');';

		foreach (array_reverse($advices) as $advice) {
			$advice_number ++;
			$type = $advice[0];

			if (self::DEBUG) {
				echo '<h4>' . $type . ' => ' . print_r($advice[1], true) . '</h4>';
			}

			/** @var $advice_class_name string */
			/** @var $advice_method_name string */
			/** @var $advice_function_name string */
			/** @var $advice_parameters string[] */
			/** @var $advice_string string [$object_, 'methodName') | 'functionName' */
			/** @var $advice_has_return boolean */
			/** @var $is_advice_static boolean */
			[
				$advice_class_name, $advice_method_name, $advice_function_name,
				$advice_parameters, $advice_string, $advice_has_return, $is_advice_static
			] = $this->decodeAdvice($advice[1], $class_name);

			// $advice_parameters_string, $joinpoint_code
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
					$joinpoint_parameters_string = '[';
					$joinpoint_string_parameters = '';
					foreach (array_values($parameters) as $key => $name) {
						if ($key) $joinpoint_parameters_string .= ', ';
						$joinpoint_parameters_string .= $key . ' => &$' . $name;
						$joinpoint_string_parameters .= ', ' . Q . $name . Q . ' => &$' . $name;
					}
					$joinpoint_parameters_string .= $joinpoint_string_parameters . ']';
					switch ($type) {
						case 'after':
							$joinpoint_code = $i2 . '$joinpoint_ = new \ITRocks\Framework\AOP\Joinpoint\After_Method('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string
								. ', $result_, ' . $advice_string
								. $i2 . ');';
							break;
						case 'around':
							$process_callback = ($methods[$method_name]->class->name === $this->class->name)
								? ($method_name . '_' . $count)
								: $method_name;
							$joinpoint_code = $i2 . '$joinpoint_ = new \ITRocks\Framework\AOP\Joinpoint\Around_Method('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string
								. ', $result_, ' . $advice_string . ', ' . Q . $process_callback . Q
								. $i2 . ');';
							break;
						case 'before':
							$joinpoint_code = $i2 . '$joinpoint_ = new \ITRocks\Framework\AOP\Joinpoint\Before_Method('
								. $i3 . '__CLASS__, ' . $pointcut_string . ', ' . $joinpoint_parameters_string
								. ', $result_, ' . $advice_string
								. $i2 . ');';
							break;
					}
				}
			}
			else {
				$advice_parameters_string = '';
			}

			$advice_code = $this->generateAdviceCode(
				$advice, $advice_class_name, $advice_method_name, $advice_function_name,
				$advice_parameters_string, $advice_has_return, $is_advice_static, $joinpoint_code, $i2,
				'$result_', ($type === 'around') ? $call_code : ''
			);

			switch ($type) {
				case 'after':
					if ($joinpoint_code) {
						$advice_code .= $i2 . 'if ($joinpoint_->stop) return'
							. ($joinpoint_has_return ? ' isset($result_) ? $result_ : null;' : ';');
					}
					$after_code[] = $advice_code;
					break;
				case 'around':
					$my_prototype = ($advice_number === $advices_count)
						? $prototype
						: str_replace($method_name, $method_name . '_' . $count , $prototype);
					$result .= substr($indent, 1) . $my_prototype . substr($i2, 1)
						. $this->codeAssembly($before_code, $advice_code, $after_code, $indent)
						. LF . $i2 . 'return' . ($joinpoint_has_return ? '$result_;' : ';')
						. $indent . '}' . LF;
					if ($advice_number < $advices_count) {
						$count ++;
					}
					$before_code = [];
					$after_code  = [];
					break;
				case 'before':
					if ($advice_has_return && $joinpoint_has_return) {
						$advice_code .= $i2 . 'if (isset($result_)) return $result_;';
					}
					if ($joinpoint_code) {
						$advice_code .= $i2 . 'if ($joinpoint_->stop) return'
							. ($joinpoint_has_return ? ' isset($result_) ? $result_ : null;' : ';');
					}
					$before_code[] = $advice_code;
					break;
			}
		}
		if ($before_code || $after_code) {
			$result .= substr($indent, 1) . $prototype . substr($i2, 1)
				. $this->codeAssembly($before_code, $call_code, $after_code, $indent)
				. LF . $i2 . 'return' . ($joinpoint_has_return ? ' $result_;' : ';')
				. $indent . '}' . LF;
		}

		$this->class->source = $this->class->source->setSource(preg_replace(
			$preg_expr,
			'$1$2/* $4 */ private $5function $6$7_' . $count . '$8$9',
			$buffer
		), false);

		return $result;
	}

}
