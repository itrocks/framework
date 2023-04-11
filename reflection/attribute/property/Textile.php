<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;

/**
 * This tells that the property should be rendered using textile parsing
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Textile
{
	use Common;
	use Has_Boolean_Value;

}
