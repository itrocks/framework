<?php
namespace ITRocks\Framework\Layout\Structure\Draw;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * A vertical line
 */
class Vertical_Line extends Element
{

	//--------------------------------------------------------------------------------------- $height
	/**
	 * The height of the object, in mm
	 * With default value
	 *
	 * @todo remove this default value when height is always set for the rectangle by the designer
	 * @var float
	 */
	public $height = 25;

	//----------------------------------------------------------------------------------- DUMP_SYMBOL
	const DUMP_SYMBOL = '|';

}
