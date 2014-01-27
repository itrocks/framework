<?php
namespace SAF\Framework;

	/**
	 * The ge
	 */
class Before_Method_Joinpoint
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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name  string
	 * @param $object      object
	 * @param $method_name string
	 */
	public function __construct($class_name, $object, $method_name, $advice)
	{
		$this->class_name  = $class_name;
		$this->object      = $object;
		$this->method_name = $method_name;
		$this->advice      = $advice;
	}

}
