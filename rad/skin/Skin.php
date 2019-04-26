<?php
namespace ITRocks\Framework\RAD;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A skin for the application
 */
class Skin
{
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

}
