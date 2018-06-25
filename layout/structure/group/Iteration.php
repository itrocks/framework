<?php
namespace ITRocks\Framework\Layout\Structure\Group;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * An iteration is a "line" (or column) of data into a group
 */
class Iteration extends Element
{

	//------------------------------------------------------------------------------------- $elements
	/**
	 * @var Element[]
	 */
	public $elements;

	//--------------------------------------------------------------------------------------- $number
	/**
	 * Iteration number : 0..n
	 *
	 * @var integer
	 */
	public $number;

}
