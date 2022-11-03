<?php
namespace ITRocks\Framework\Layout\Structure\Element;

use ITRocks\Framework\Layout\Structure\Element;

/**
 * Element with a init method to calculate its non-initialized attributes
 *
 * @extends Element
 */
interface Has_Init
{

	//------------------------------------------------------------------------------------------ init
	public function init();

}
