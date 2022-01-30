<?php
namespace ITRocks\Framework\Traits;

/**
 * A business trait for class that need a number and a name
 *
 * @representative number, name
 */
trait Has_Number_And_Name
{
	use Has_Name { Has_Name::__toString as private hasNameToString; }
	use Has_Number { Has_Number::__toString as private hasNumberToString; }

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return trim($this->number . SP . $this->name);
	}

}
