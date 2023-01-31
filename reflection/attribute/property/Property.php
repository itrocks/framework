<?php
namespace ITRocks\Framework\Reflection\Attribute;

use ITRocks\Framework\Reflection\Attribute;
use ITRocks\Framework\Reflection\Interfaces\Reflection;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Property;

abstract class Property extends Attribute
{

	//------------------------------------------------------------------------------------- $property
	public Reflection_Property $property;

	//------------------------------------------------------------------------------------- setTarget
	public function setTarget(Reflection|Reflection_Property $target) : void
	{
		$this->property = $target;
	}

}
