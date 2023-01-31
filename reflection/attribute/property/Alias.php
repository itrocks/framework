<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

#[Attribute]
class Alias extends Property
{
	use Has_String_Value;

	//------------------------------------------------------------------------------------- setTarget
	public function setTarget(Reflection|Reflection_Property $target) : void
	{
		$this->property = $target;
		if ($this->value) return;
		$this->value = $target->getName();
	}

}
