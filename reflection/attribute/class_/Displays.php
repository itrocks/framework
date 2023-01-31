<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection\Attribute\Class_;
use ITRocks\Framework\Reflection\Attribute\Has_String_Value;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

#[Attribute]
class Displays extends Class_
{
	use Has_String_Value;

	//-------------------------------------------------------------------------------------- setFinal
	public function setFinal(Reflection_Class $class) : void
	{
		if ($this->value) return;
		$this->value = str_replace('_', SP, Store::of($class)->value);;
	}

}
