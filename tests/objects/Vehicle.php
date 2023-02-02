<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A vehicle
 */
#[Store('test_vehicles')]
class Vehicle
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $doors
	/**
	 * @var Vehicle_Door[]
	 * @warning hasDoors
	 */
	#[Property\Component]
	public array $doors;

	//-------------------------------------------------------------------------------------- hasDoors
	/**
	 * @return boolean true if the vehicle has doors, else false
	 */
	public function hasDoors() : bool
	{
		return boolval($this->doors);
	}

}
