<?php
namespace ITRocks\Framework\Reflection\Attribute\Class_;

use Attribute;
use ITRocks\Framework\Reflection;
use ITRocks\Framework\Reflection\Attribute\Calculated;
use ITRocks\Framework\Reflection\Attribute\Has_Attributes;
use ITRocks\Framework\Reflection\Attribute\Inheritable;
use ITRocks\Framework\Reflection\Interfaces;
use ITRocks\Framework\Tools\Names;

#[Attribute]
#[Inheritable]
class Display implements Calculated
{
	use Reflection\Attribute;

	//---------------------------------------------------------------------------------------- $value
	public string $value;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $value = '')
	{
		$this->value = $value;
	}

	//------------------------------------------------------------------------------------- calculate
	public function calculate(Has_Attributes|Interfaces\Reflection $reflection) : void
	{
		if ($this->value) return;
		$this->value = str_replace('_', SP, Names::setToSingle(Store_Name::of($reflection)->value));
	}

}
