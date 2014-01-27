<?php
namespace SAF\Framework;

/**
 * The ge
 */
class Property_Write_Joinpoint
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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name    string
	 * @param $object        object
	 * @param $property_name string
	 */
	public function __construct($class_name, $object, $property_name)
	{
		$this->class_name    = $class_name;
		$this->object        = $object;
		$this->property_name = $property_name;
	}

}
