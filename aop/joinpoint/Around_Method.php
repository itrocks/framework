<?php
namespace ITRocks\Framework\AOP\Joinpoint;

use ReflectionMethod;

/**
 * Around method joinpoint
 */
class Around_Method extends Method_Joinpoint
{

	//------------------------------------------------------------------------------- $process_method
	private string $process_method;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $pointcut string[]|object[]
	 * @param $advice   string[]|object[]|string
	 */
	public function __construct(
		string $class_name, array $pointcut, array $parameters, mixed &$result, array|string $advice,
		string $process_method
	) {
		parent::__construct($class_name, $pointcut, $parameters, $result, $advice);
		$this->process_method = $process_method;
	}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Launch the method that which call was replaced by the advice
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $args mixed The arguments the original method was expected to receive
	 */
	public function process(mixed... $args) : mixed
	{
		$class_name = is_string($this->pointcut[0]) ? $this->pointcut[0] : get_class($this->pointcut[0]);

		if (
			($this->class_name        === $class_name)
			&& ($this->process_method === $this->pointcut[1])
		) {
			/** @noinspection PhpUnhandledExceptionInspection class and method must be valid */
			$method = (new ReflectionMethod(get_parent_class($this->class_name), $this->process_method));
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection class and method must be valid */
			$method = (new ReflectionMethod($this->class_name, $this->process_method));
		}
		// if this is a static method : invoked object is null
		$object = $this->pointcut[0];
		if (!is_object($object)) {
			$object = null;
		}
		// invoke
		if (count($args)) {
			/** @noinspection PhpUnhandledExceptionInspection method must be declared */
			$result = $method->invokeArgs($object, $args);
		}
		elseif ($this->parameters) {
			$parameters = array_slice($this->parameters, 0, count($this->parameters) / 2);
			/** @noinspection PhpUnhandledExceptionInspection method must be declared */
			$result = $method->invokeArgs($object, $parameters);
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection method must be declared */
			$result = $method->invoke($object);
		}
		return $result;
	}

}
