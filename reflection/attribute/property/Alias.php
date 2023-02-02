<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Declaring;
use ITRocks\Framework\Reflection\Attribute\Template\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Alias extends Property implements Has_Set_Declaring
{
	use Has_String_Value;

	//---------------------------------------------------------------------------------- setDeclaring
	public function setDeclaring(Reflection|Reflection_Property $reflection) : void
	{
		if ($this->value) return;
		$this->value = $reflection->getName();
	}

}
