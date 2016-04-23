<?php
namespace SAF\Framework\Dao\Option;

use SAF\Framework\Dao\Option;

/**
 * Dao key option : what class property will be used for key for objects array
 */
class Key implements Option
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var string|string[]
	 */
	public $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name string|string[]
	 */
	public function __construct($property_name)
	{
		$this->property_name = $property_name;
	}

}
