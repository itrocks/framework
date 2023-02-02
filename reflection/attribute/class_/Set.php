<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Always;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Attribute\Template\Has_Set_Final;
use ITRocks\Framework\Reflection\Attribute\Template\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Names;

#[Always, Attribute(Attribute::TARGET_CLASS), Inheritable]
class Set extends Class_ implements Has_Set_Final
{
	use Has_String_Value;

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection|Reflection_Class $reflection) : void
	{
		if ($this->value) return;
		$this->value = Names::singleToSet($reflection->getName());
	}

}
