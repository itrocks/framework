<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Traits\Has_Brand;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A model
 *
 * @display_order brand, name
 * @feature
 * @override brand @mandatory
 * @representative brand, name
 */
#[Override('name', new Alias('name'))]
class Model
{
	use Has_Brand;
	use Has_Name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->brand ? ($this->brand . SP . $this->name) : $this->name;
	}

}
