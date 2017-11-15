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
	use Mapper\Component;
	use Has_Name;

	//----------------------------------------------------------------------------------------- $door
	/**
	 * @composite
	 * @link Object
	 * @var Vehicle_Door
	 */
	public $door;

}
