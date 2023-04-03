<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The mandatory annotation validator
 */
#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Mandatory extends Property implements Has_Get_Default_Arguments, Has_Set_Final
{
	use Has_Boolean_Value;

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		if (isset($this->value)) {
			return;
		}
		$type        = $reflection->getType();
		$this->value = !(
			$type->allowsNull() || $type->isBoolean() || $type->isDateTime() || $type->isMultiple()
			|| $type->isString()
		);
	}

}
