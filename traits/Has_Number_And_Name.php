<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Representative;

/**
 * A business trait for class that need a number and a name
 */
#[Representative('number', 'name')]
trait Has_Number_And_Name
{
	use Has_Name   { Has_Name::__toString   as private hasNameToString;   }
	use Has_Number { Has_Number::__toString as private hasNumberToString; }

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return trim($this->number . SP . $this->name);
	}

}
