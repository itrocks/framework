<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Mapper;

/**
 * A vehicle door
 *
 * @store_name test_vehicle_doors
 * @validate codeValid
 */
class Vehicle_Door
{
	use Mapper\Component;

	//------------------------------------------------------------------------------------ FRONT_LEFT
	const FRONT_LEFT = 'front-left';

	//----------------------------------------------------------------------------------- FRONT_RIGHT
	const FRONT_RIGHT = 'front-right';

	//------------------------------------------------------------------------------------- REAR_LEFT
	const REAR_LEFT = 'rear-left';

	//------------------------------------------------------------------------------------ REAR_RIGHT
	const REAR_RIGHT = 'rear-right';

	//----------------------------------------------------------------------------------------- TRUNK
	const TRUNK = 'trunk';

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @length 6
	 * @var string
	 */
	public string $code = '';

	//--------------------------------------------------------------------------------------- $pieces
	/**
	 * @link Collection
	 * @var Vehicle_Door_Piece[]
	 */
	public array $pieces;

	//----------------------------------------------------------------------------------------- $side
	/**
	 * @values self::const
	 * @var string
	 * @warning sideNotTrunk
	 */
	public string $side;

	//-------------------------------------------------------------------------------------- $vehicle
	/**
	 * @composite
	 * @link Object
	 * @var Vehicle
	 */
	public Vehicle $vehicle;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return Loc::tr($this->side);
	}

	//------------------------------------------------------------------------------------- codeValid
	/**
	 * @return string|true
	 */
	public function codeValid() : bool|string
	{
		return ($this->code !== '')
			? true
			: (Loc::tr('code is not valid') . ' : ' . Loc::tr('must not be empty'));
	}

	//---------------------------------------------------------------------------------- sideNotTrunk
	/**
	 * @return string|true
	 */
	public function sideNotTrunk() : bool|string
	{
		return ($this->side === self::TRUNK) ? Loc::tr('side should not be trunk') : true;
	}

}
