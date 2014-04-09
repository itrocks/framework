<?php
namespace SAF\AOP\Joinpoint;

use ReflectionMethod;

/**
 * Around method joinpoint
 */
class Around_Method extends Method_Joinpoint
{

	//------------------------------------------------------------------------------- $process_method
	/**
	 * @var string
	 */
	private $process_method;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name     string
	 * @param $pointcut       string[]|object[]
	 * @param $parameters     array
	 * @param $advice         string[]|object[]|string
	 * @param $process_method string
	 */
	public function __construct($class_name, $pointcut, $parameters, $advice, $process_method)
	{
		$this->advice         = $advice;
		$this->class_name     = $class_name;
		$this->parameters     = $parameters;
		$this->pointcut       = $pointcut;
		$this->process_method = $process_method;
	}

	//--------------------------------------------------------------------------------------- process
	/**
	 * Launch the method that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original method was expected to receive
	 * @return mixed
	 */
	public function process($args = null)
	{
		$method = (new ReflectionMethod($this->class_name, $this->process_method));
		// the method must be accessible to invoke it
		if (!$method->isPublic()) {
			$not_accessible = true;
			$method->setAccessible(true);
		}
		// if this is a static method : invoked object is null
		$object = $this->pointcut[0];
		if (!is_object($object)) {
			$object = null;
		}
		// invoke
		if (func_num_args()) {
			$result = $method->invokeArgs($object, func_get_args());
		}
		elseif ($this->parameters) {
			$parameters = array_slice($this->parameters, 0, count($this->parameters) / 2);
			$result = $method->invokeArgs($object, $parameters);
		}
		else {
			$result = $method->invoke($object);
		}
		// the method must be not accessible again
		if (isset($not_accessible)) {
			$method->setAccessible(false);
		}
		return $result;
	}

}
