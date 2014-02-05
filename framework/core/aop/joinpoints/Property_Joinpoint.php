<?php
namespace SAF\AOP;

//------------------------------------------------------------------------------ Property_Joinpoint
/**
 * Property joinpoint
 */
class Property_Joinpoint extends Joinpoint
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * @var string
	 */
	public $class_name;

	//--------------------------------------------------------------------------------------- $object
	/**
	 * @var object
	 */
	public $object;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string
	 */
	public $property_name;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @var mixed
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $pointcut      string[]|object[]
	 * @param $value         mixed
	 * @param $advice        callable
	 */
	public function __construct($class_name, $pointcut, &$value, $advice)
	{
		$this->class_name    = $class_name;
		$this->object        = is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->pointcut      = $pointcut;
		$this->property_name = $pointcut[1];
		$this->value         = &$value;
		$this->advice        = $advice;
	}

	//----------------------------------------------------------------------------------- getProperty
	/**
	 * @return Reflection_Property
	 */
	public function getProperty()
	{
		return new Reflection_Property(
			is_object($this->pointcut[0]) ? get_class($this->pointcut[0]) : $this->pointcut[0],
			$this->pointcut[1]
		);
	}

}
