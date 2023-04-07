<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Callable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;

/**
 * #Default_ [[\Class\Namespace\]Class_Name::]methodName
 * Identifies a method that gets the default value for the property.
 * The Property will be sent as an argument to this callable.
 *
 * If no #Default attribute is set for an object property, looks at the class #Default attribute
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Default_ implements Has_Set_Final
{
	use Common;
	use Has_Callable;

}
