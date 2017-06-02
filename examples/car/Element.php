<?php
namespace ITRocks\Framework\Examples\Car;

use ITRocks\Framework\Traits\Has_Code;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A car element
 *
 * @set Example_Car_Elements
 */
class Element
{
	use Has_Code;
	use Has_Name;

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
