<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Traits\Has_Code_And_Name;

/**
 * A simple test object
 */
#[Store]
class Simple_Component
{
	use Component;
	use Has_Code_And_Name;

	//------------------------------------------------------------------------------------ $composite
	#[Composite]
	public Has_Collection $composite;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @param $code string
	 * @param $name string
	 */
	public function __construct(string $code = '', string $name = '')
	{
		if ($code !== '') $this->code = $code;
		if ($name !== '') $this->name = $name;
	}

}
