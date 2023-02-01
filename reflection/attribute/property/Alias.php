<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Alias extends Property
{
	use Has_String_Value;

	//---------------------------------------------------------------------------------- setDeclaring
	public function setDeclaring(Reflection_Property $property) : void
	{
		if ($this->value) return;
		$this->value = $property->getName();
	}

}
