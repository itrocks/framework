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
	/**
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------------- $disable
	/**
	 * The advice can set this to true in order to stop to definitively disable AOP on this property
	 * This disables all AOP advices, not only the current one : this is to be used for optimization
	 * purpose on property you known they need AOP one time only
	 *
	 * @var boolean
	 */
	public $disable = false;

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
	 * @param $class_name string
	 * @param $pointcut   string[]|object[]
	 * @param $value      mixed
	 * @param $stored     mixed
	 * @param $advice     callable
	 */
	public function __construct($class_name, array $pointcut, &$value, &$stored, callable $advice)
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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Property
	 */
	public function getProperty()
	{
		/** @noinspection PhpUnhandledExceptionInspection $pointcut must be a valid property */
		return new Reflection_Property(
			is_object($this->pointcut[0]) ? get_class($this->pointcut[0]) : $this->pointcut[0],
			$this->pointcut[1]
		);
	}

}
