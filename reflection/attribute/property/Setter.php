<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Property;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Default_Callable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;
use ReflectionException;

/**
 * Tells a method name that is the setter for that property.
 * The setter will be called each time the program writes the property.
 */
#[Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Setter extends Property implements Has_Set_Final
{
	use Has_Default_Callable;

	//-------------------------------------------------------------------------------------- setFinal
	/**
	 * @throws ReflectionException
	 */
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		$this->getDefaultMethod('set', $reflection);
	}

}
