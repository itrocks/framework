<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Traits\Has_Brand;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A model
 *
 * @feature
 * @representative brand, name
 */
#[
	Display_Order('brand', 'name'),
	Override('brand', new Mandatory),
	Override('name',  new Alias('name'))
]
class Model
{
	use Has_Brand;
	use Has_Name;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->brand ? ($this->brand . SP . $this->name) : $this->name;
	}

}
