<?php
namespace SAF\AOP;

/**
 * The joinpoint on property write
 */
class Property_Write_Joinpoint extends Property_Joinpoint
{

	//----------------------------------------------------------------------------------- $last_value
	/**
	 * @var mixed
	 */
	public $last_value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $pointcut      string[]|object[]
	 * @param $value         mixed
	 * @param $last_value    mixed
	 * @param $advice        callable
	 */
	public function __construct($class_name, $pointcut, &$value, $last_value, $advice)
	{
		$this->class_name    = $class_name;
		$this->last_value    = $last_value;
		$this->object        = is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->pointcut      = $pointcut;
		$this->property_name = $pointcut[1];
		$this->value         = &$value;
		$this->advice        = $advice;
	}
}
