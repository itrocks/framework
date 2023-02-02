<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;

#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Component extends Property
{
	use Has_Boolean_Value;

}
