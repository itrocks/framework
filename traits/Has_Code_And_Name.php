<?php
namespace ITRocks\Framework\Traits;

/**
 * A business trait for class that need a code and a name
 *
 * @representative code, name
 */
trait Has_Code_And_Name
{
	use Has_Code;
	use Has_Name { __toString as private hasNameToString; }

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return trim($this->code . SP . $this->name);
	}

}
