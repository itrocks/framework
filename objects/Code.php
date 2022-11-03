<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * Standard basic codes, with a code and a full name
 *
 * @business
 * @deprecated use Has_Code_and_Name
 * @see Has_Code_And_Name
 */
abstract class Code
{
	use Has_Code_And_Name;

	//---------------------------------------------------------------------------------------- equals
	/**
	 * Returns true if the two codes are equal :
	 * - if at least one of them has a code and the codes are equal : it is the same
	 * - else if they have no code and the names are equal : it is the same
	 *
	 * @param $code Code
	 * @return boolean
	 */
	public function equals(Code $code)
	{
		return $this->sameAs($code);
	}

}
