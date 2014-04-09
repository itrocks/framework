<?php
namespace SAF\AOP\Joinpoint;

use SAF\AOP\Joinpoint;
use SAF\Framework\Reflection\Reflection_Property;

/**
 * Property joinpoint
 */
class Property extends Joinpoint
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

	//--------------------------------------------------------------------------------------- $stored
	/**
	 * @var mixed
	 */
	public $stored;

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
	 * @param $stored        mixed
	 * @param $advice        callable
	 */
	public function __construct($class_name, $pointcut, &$value, &$stored, $advice)
	{
		$this->advice        = $advice;
		$this->class_name    = $class_name;
		$this->object        = is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->pointcut      = $pointcut;
		$this->property_name = $pointcut[1];
		$this->stored        = &$stored;
		$this->value         = &$value;
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
