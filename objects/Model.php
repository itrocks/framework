<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Traits\Has_Brand;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A model
 *
 * @display_order brand, name
 * @feature
 * @override brand @mandatory
 * @override name @alias model
 * @representative brand, name
 */
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
		return $this->brand ? ($this->brand . SP . $this->name) : strval($this->name);
	}

}
