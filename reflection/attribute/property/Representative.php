<?php
namespace ITRocks\Framework\Reflection\Attribute\Property;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the linked object, when we have @link Collection.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
#[Always, Attribute(Attribute::TARGET_PROPERTY), Inheritable]
class Representative implements Has_Set_Final
{
	use Common;
	use Common\Representative;

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Property $reflection) : void
	{
		$this->class      = $reflection->getType()->asReflectionClass();
		$this->properties = Replaces_Annotations::replaceProperties($this->getProperties());
		$this->values     = array_keys($this->properties);
	}

}
