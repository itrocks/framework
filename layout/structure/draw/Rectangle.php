<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * A rectangle
 */
class Rectangle extends Element
{

	//--------------------------------------------------------------------------------------- $height
	/**
	 * The height of the object, in mm
	 * With default value
	 *
	 * @todo remove this default value when height is always set for the rectangle by the designer
	 * @var float
	 */
	public $height = 12.5;

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = ']';

}
