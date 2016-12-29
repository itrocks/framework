<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Mapper;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A piece of vehicle door
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
