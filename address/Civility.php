<?php
namespace ITRocks\Framework\Address;

use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A physical person civility
 *
 * @feature
 */
class Civility
{
	use Has_Code_And_Name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->code ?? '';
	}

}
