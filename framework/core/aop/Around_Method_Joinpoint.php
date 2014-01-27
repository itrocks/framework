<?php
namespace SAF\Framework;

/**
 * The ge
 */
class Around_Method_Joinpoint
{

	//--------------------------------------------------------------------------------------- $advice
	/**
	 * @var string|string[]|array
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
	 * @var array
	 */
	private $process_callback;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
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
