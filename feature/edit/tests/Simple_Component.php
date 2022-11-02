<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A simple test object
 */
class Simple_Component
{
	use Component;
	use Has_Code_And_Name;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * @composite
	 * @link Object
	 * @var Has_Collection
	 */
	public Has_Collection $composite;

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
