<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Constant_Or_Callable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Declaring;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;

#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Unit extends Property implements Has_Set_Declaring, Has_Set_Final
{
	use Has_Constant_Or_Callable;

}
