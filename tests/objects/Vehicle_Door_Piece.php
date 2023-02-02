<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A piece of vehicle door
 */
#[Store('test_vehicle_door_pieces')]
class Vehicle_Door_Piece
{
	use Has_Name;
	use Mapper\Component;

	//----------------------------------------------------------------------------------------- $door
	#[Property\Composite]
	public Vehicle_Door $door;

}
