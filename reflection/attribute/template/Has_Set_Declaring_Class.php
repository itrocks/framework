<?php
namespace ITRocks\Framework\Reflection\Attribute\Template;

use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

interface Has_Set_Declaring_Class
{

	//----------------------------------------------------------------------------- setDeclaringClass
	public function setDeclaringClass(Reflection_Class $class) : void;

}
