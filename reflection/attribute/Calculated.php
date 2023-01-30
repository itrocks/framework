<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Interfaces\Reflection;

interface Calculated
{

	//------------------------------------------------------------------------------------- calculate
	public function calculate(Has_Attributes|Reflection $reflection) : void;

}
