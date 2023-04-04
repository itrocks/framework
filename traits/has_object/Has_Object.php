<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * For all classes working on an object as representative value
 *
 * This object must embed a __toString() method for string representation
 */
#[Representative('object')]
trait Has_Object
{

	//--------------------------------------------------------------------------------------- $object
	#[Mandatory]
	public object $object;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return strval($this->object);
	}

}
