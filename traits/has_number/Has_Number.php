<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;

/**
 * For all classes having a number
 */
#[Representative('number')]
trait Has_Number
{

	//--------------------------------------------------------------------------------------- $number
	#[Mandatory]
	public string $number = '';

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->number;
	}

}
