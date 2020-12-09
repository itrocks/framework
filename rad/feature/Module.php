<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Traits\Has_Name;

/**
 * @override name @translate common
 * @store_name rad_feature_modules
 */
class Module
{
	use Has_Name;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->name ? Loc::tr($this->name) : '';
	}

}
