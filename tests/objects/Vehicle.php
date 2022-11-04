<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A vehicle
 *
 * @store_name test_vehicles
 */
class Vehicle
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $doors
	/**
	 * @link Collection
	 * @var Vehicle_Door[]
	 * @warning hasDoors
	 */
	public array $doors;

	//-------------------------------------------------------------------------------------- hasDoors
	/**
	 * @return boolean true if the vehicle has doors, else false
	 */
	public function hasDoors() : bool
	{
		return (bool)$this->doors;
	}

}
