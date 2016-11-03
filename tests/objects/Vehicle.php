<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A vehicle
 */
class Vehicle
{
	use Has_Name;

	//---------------------------------------------------------------------------------------- $doors
	/**
	 * @link Collection
	 * @var Vehicle_Door[]
	 */
	public $doors;

}
