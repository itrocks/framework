<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Constant_Or_Callable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;

#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Unit implements Has_Set_Final
{
	use Common;
	use Has_Constant_Or_Callable;

}
