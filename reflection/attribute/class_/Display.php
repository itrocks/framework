<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\Tools\Names;

#[Attribute]
class Display extends Class_
{
	use Has_String_Value;

	//------------------------------------------------------------------------------------- setTarget
	public function setTarget(Reflection|Reflection_Class $target) : void
	{
		$this->class = $target;
		if ($this->value) return;
		$this->value = str_replace('_', SP, Names::setToSingle(Store::of($target)->value));
	}

}
