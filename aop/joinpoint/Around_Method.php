<?php
namespace ITRocks\Framework\AOP\Joinpoint;

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
	public function __construct(
		$class_name, array $pointcut, array $parameters, $advice, $process_method
	) {
		parent::__construct($class_name, $pointcut, $parameters, $advice);
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
		if (
			($this->class_name        == get_class($this->pointcut[0]))
			&& ($this->process_method == $this->pointcut[1])
		) {
			$method = (new ReflectionMethod(get_parent_class($this->class_name), $this->process_method));
		}
		else {
			$method = (new ReflectionMethod($this->class_name, $this->process_method));
		}
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
