<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Traits\Has_Code_And_Name;
use ITRocks\Framework\Traits\Is_Immutable;

/**
 * A country
 *
 * @feature
 * @list code, name
 * @representative name
 */
#[Override('code', new Mandatory), Store]
class Country
{
	use Has_Code_And_Name;
	use Is_Immutable;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->name;
	}

}
