<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Boolean_Value;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Get_Default_Arguments;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The mandatory annotation validator
 */
#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Mandatory implements Has_Get_Default_Arguments
{
	use Common;
	use Has_Boolean_Value { getDefaultArguments as private; }

	//--------------------------------------------------------------------------- getDefaultArguments
	public static function getDefaultArguments(Reflection|Reflection_Property $reflection) : array
	{
		$type = $reflection->getType();
		return [!(
			$type->allowsNull() || $type->isBoolean() || $type->isDateTime() || $type->isMultiple()
			|| $type->isString()
		)];
	}

}
