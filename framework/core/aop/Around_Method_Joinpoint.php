<?php
namespace SAF\Framework;

/**
 * Around method joinpoint
 */
class Around_Method_Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @var string[]|object[]|string
	 */
	public $advice;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $method_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//----------------------------------------------------------------------------- $process_callback
	/**
	 * @var string[]|object[]
	 */
	private $process_callback;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name       string
	 * @param $object           object
	 * @param $method_name      string
	 * @param $advice           string[]|object[]|string
	 * @param $process_callback string[]|object[]
	 */
	public function __construct($class_name, $object, $method_name, $advice, $process_callback)
	{
		$this->class_name       = $class_name;
		$this->object           = $object;
		$this->method_name      = $method_name;
		$this->advice           = $advice;
		$this->process_callback = $process_callback;
	}

	//-------------------------------------------------------------------------------------- $process
	/**
	 * Launch the method that which call was replaced by the advice
	 *
	 * @param $args mixed The arguments the original method was expected to receive
	 * @return mixed
	 */
	public function process($args = null)
	{
		return call_user_func_array($this->process_callback, func_get_args());
	}

}
