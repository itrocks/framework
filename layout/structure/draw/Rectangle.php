<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * A rectangle
 */
class Rectangle extends Element
{

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = ']';

	//--------------------------------------------------------------------------------------- $height
	/**
	 * The height of the object, in mm
	 * With default value
	 *
	 * @var float
	 */
	public float $height = 12.5;

}
