<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Attribute;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;

abstract class Class_ extends Attribute
{

	//---------------------------------------------------------------------------------------- $class
	public Reflection_Class $class;

	//------------------------------------------------------------------------------------- setTarget
	public function setTarget(Reflection|Reflection_Class $target) : void
	{
		$this->class = $target;
	}

}
