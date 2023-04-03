<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;

#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Multiline extends Property implements Has_Get_Default_Arguments
{
	use Has_Boolean_Value;

}
