<?php
namespace ITRocks\Framework\Feature\Cards\Property;

use ITRocks\Framework\Feature\Cards\Property;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Has_Ordering;

/**
 * Card group property
 */
#[Store(false)]
class Group extends Property
{
	use Has_Ordering;

}
