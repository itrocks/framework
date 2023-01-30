<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A tag is a keyword to make search easier
 */
#[Store_Name('rad_tags')]
class Tag
{
	use Has_Name;

}
