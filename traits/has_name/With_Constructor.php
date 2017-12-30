<?php
namespace ITRocks\Framework\Traits\Has_Name;

use ITRocks\Framework\Traits\Has_Name;

/**
 * For classes that have name with a constructor
 */
trait With_Constructor
{
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A constructor for your Has_Name class
	 *
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) {
			$this->name = $name;
		}
	}

}
