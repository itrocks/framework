<?php
namespace ITRocks\Framework\Layout\Structure\Element;

use ITRocks\Framework\Layout\Structure\Element;
use ITRocks\Framework\Reflection\Attribute\Class_\Extends_;

/**
 * Element with an init method to calculate its non-initialized attributes
 */
#[Extends_(Element::class)]
interface Has_Init
{

	//------------------------------------------------------------------------------------------ init
	public function init() : void;

}
