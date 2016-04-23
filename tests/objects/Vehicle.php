<?php
namespace SAF\Framework\Tests\Objects;

use SAF\Framework\Traits\Has_Name;

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
