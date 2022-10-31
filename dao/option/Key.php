<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao\Option;

/**
 * Dao key option : what class property will be used for key for objects array
 */
class Key implements Option
{

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * @var callable|string|string[]
	 */
	public array|string $property_name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $property_name callable|string|string[]
	 */
	public function __construct(array|callable|string $property_name)
	{
		$this->property_name = $property_name;
	}

}
