<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A piece of vehicle door
 */
class Vehicle_Door_Piece
{
	use Component;
	use Has_Name;

	//----------------------------------------------------------------------------------------- $door
	/**
	 * @composite
	 * @link Object
	 * @var Vehicle_Door
	 */
	public $door;

}
