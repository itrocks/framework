<?php
namespace ITRocks\Framework\Widget\Edit\Tests;

use ITRocks\Framework\Traits\Has_Code;
use ITRocks\Framework\Traits\Has_Name;

/**
 * A simple test object
 */
class Simple
{
	use Has_Code;
	use Has_Name;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @param $code string
	 * @param $name string
	 */
	public function __construct($code = null, $name = null)
	{
		if (isset($code)) $this->code = $code;
		if (isset($name)) $this->name = $name;
	}

}
