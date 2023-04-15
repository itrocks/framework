<?php
namespace ITRocks\Framework\AOP\Joinpoint;

use ITRocks\Framework\AOP\Joinpoint;
use ITRocks\Framework\Reflection\Reflection_Property;

/**
 * Property joinpoint
 */
abstract class Property_Joinpoint extends Joinpoint
{

	//----------------------------------------------------------------------------------- $class_name
	public string $class_name;

	//-------------------------------------------------------------------------------------- $disable
	/**
	 * The advice can set this to true in order to stop to definitively disable AOP on this property
	 * This disables all AOP advices, not only the current one : this is to be used for optimization
	 * purpose on property you know they need AOP one time only
	 */
	public bool $disable = false;

	//--------------------------------------------------------------------------------------- $object
	public object $object;

	//-------------------------------------------------------------------------------- $property_name
	public string $property_name;

	//--------------------------------------------------------------------------------------- $stored
	public mixed $stored;

	//---------------------------------------------------------------------------------------- $value
	public mixed $value;

	//----------------------------------------------------------------------------------- __construct
	/** @param $pointcut object[]|string[] */
	public function __construct(
		string $class_name, array $pointcut, mixed &$value, mixed &$stored, callable $advice
	) {
		$this->advice        =  $advice;
		$this->class_name    =  $class_name;
		$this->object        =  is_object($pointcut[0]) ? $pointcut[0] : null;
		$this->pointcut      =  $pointcut;
		$this->property_name =  $pointcut[1];
		$this->stored        =& $stored;
		$this->value         =& $value;
	}

	//----------------------------------------------------------------------------------- getProperty
	public function getProperty() : Reflection_Property
	{
		/** @noinspection PhpUnhandledExceptionInspection $pointcut must be a valid property */
		return new Reflection_Property(
			is_object($this->pointcut[0]) ? get_class($this->pointcut[0]) : $this->pointcut[0],
			$this->pointcut[1]
		);
	}

}
