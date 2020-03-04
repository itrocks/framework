<?php
namespace ITRocks\Framework\Traits;

/**
 * A business trait for class that need a number and a name
 *
 * @representative number, name
 */
trait Has_Number_And_Name
{
	use Has_Number;
	use Has_Name { __toString as private hasNameToString; }

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim($this->number . SP . $this->name);
	}

}
