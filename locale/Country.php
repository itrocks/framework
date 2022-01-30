<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Traits\Has_Code_And_Name;
use ITRocks\Framework\Traits\Is_Immutable;

/**
 * A country
 *
 * @business
 * @feature
 * @list code, name
 * @override code @mandatory
 * @representative name
 */
class Country
{
	use Has_Code_And_Name;
	use Is_Immutable;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return strval($this->name);
	}

}
