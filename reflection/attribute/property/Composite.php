<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;

#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Composite
{
	use Common;
	use Has_Boolean_Value;

}
