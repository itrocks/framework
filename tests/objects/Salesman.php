<?php
namespace ITRocks\Framework\Tests\Objects;

use ITRocks\Framework\Traits\Has_Name;

/**
 * A salesman class
 *
 * @store_name test_salesmen
 */
class Salesman
{
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Salesman constructor
	 *
	 * @param $name string
	 */
	public function __construct($name = null)
	{
		if (isset($name)) $this->name = $name;
	}

}
