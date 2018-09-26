<?php
namespace ITRocks\Framework\Examples\Car;

use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A car element
 *
 * @store_name example_car_elements
 */
class Element
{
	use Has_Code_And_Name;

	//------------------------------------------------------------------------------------- $position
	/**
	 * @values front, rear
	 * @var string
	 */
	public $position;

	//----------------------------------------------------------------------------------------- $side
	/**
	 * @values left, right
	 * @var string
	 */
	public $side;

}
