<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;

#[Attribute(Attribute::TARGET_CLASS), Inheritable]
class All extends Property implements Has_Get_Default_Arguments
{
	use Has_Boolean_Value;

}
