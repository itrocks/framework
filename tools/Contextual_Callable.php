<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Reflection\Reflection_Method;

/**
 * This is a contextual flexible callable class
 *
 * Construct this with a context object and a callable string
 * and get the object or class name and method name in return
 */
class Contextual_Callable
{

	//------------------------------------------------------------------------------ $callable_string
	private string $callable_string;

	//-------------------------------------------------------------------------------------- $context
	private object $context;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $callable_string = null, object $context = null)
	{
		if (isset($callable_string)) $this->callable_string = $callable_string;
		if (isset($context))         $this->context         = $context;
	}

	//------------------------------------------------------------------------------------------ call
	public function call(mixed ...$args) : mixed
	{
		return func_num_args()
			? call_user_func_array($this->getCallable(), func_get_args())
			: call_user_func($this->getCallable());
	}

	//----------------------------------------------------------------------------------- getCallable
	public function getCallable() : callable
	{
		if ($i = strpos($this->callable_string, '::')) {
			$class_name = substr($this->callable_string, 0, $i);
			switch ($class_name) {
				case '$this':
					$class_name = $this->context;
					break;
				case 'self':
					$class_name = get_class($this->context);
					break;
				default:
					$class_name = Namespaces::defaultFullClassName($class_name, get_class($this->context));
			}
			$method_name = substr($this->callable_string, $i + 2);
		}
		else {
			$class_name = get_class($this->context);
			$method_name = $this->callable_string;
			/** @noinspection PhpUnhandledExceptionInspection callable must be a valid */
			if (!(new Reflection_Method($class_name, $method_name))->isStatic()) {
				$class_name = $this->context;
			}
		}
		return [$class_name, $method_name];
	}

}
