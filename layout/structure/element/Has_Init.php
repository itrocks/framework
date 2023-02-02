<?php
namespace ITRocks\Framework\Layout\Structure\Element;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Reflection\Attribute\Class_\Extend;

/**
 * Element with an init method to calculate its non-initialized attributes
 */
#[Extend(Element::class)]
interface Has_Init
{

	//------------------------------------------------------------------------------------------ init
	public function init() : void;

}
