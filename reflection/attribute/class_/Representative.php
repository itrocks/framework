<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Annotation\Sets\Replaces_Annotations;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Common;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

/**
 * The 'representative' annotation stores the list of properties which values are representative
 * of the object.
 *
 * The __toString() method of the class should return the representative properties values.
 *
 * @example a property called 'name' could be a representative property for a unique named object
 */
#[Always, Attribute(Attribute::TARGET_CLASS), Inheritable]
class Representative extends Class_ implements Has_Set_Final
{
	use Common\Representative;

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Class $reflection) : void
	{
		$this->class = $reflection;
		if (!$this->values) {
			$this->properties = [];
			foreach ($reflection->getProperties([T_EXTENDS, T_USE]) as $property) {
				if (!$property->isStatic() && !$property->getType()->isMultiple()) {
					$this->properties[$property->getName()] = $property;
				}
			}
		}
		$this->properties = Replaces_Annotations::replaceProperties($this->getProperties());
		$this->values     = array_keys($this->properties);
	}

}
