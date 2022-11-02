<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A simple test object
 */
class Simple
{
	use Has_Code_And_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @param $code string|null
	 * @param $name string|null
	 */
	public function __construct(string $code = null, string $name = null)
	{
		if (isset($code)) $this->code = $code;
		if (isset($name)) $this->name = $name;
	}

}
