<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A piece of vehicle door
 *
 * @store_name test_vehicle_door_pieces
 */
class Vehicle_Door_Piece
{
	use Has_Name;
	use Mapper\Component;

	//----------------------------------------------------------------------------------------- $door
	/**
	 * @composite
	 * @link Object
	 * @var Vehicle_Door
	 */
	public Vehicle_Door $door;

}
