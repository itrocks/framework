<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Traits\Has_Code;

/**
 * A simple test object
 */
#[Store]
class Component_Has_Map
{
	use Component;
	use Has_Code;

	//------------------------------------------------------------------------------------ $composite
	#[Composite]
	public Has_Collection $composite;

	//----------------------------------------------------------------------------------- $simple_map
	/**
	 * @var Simple[]
	 */
	public array $simple_map = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code       string
	 * @param $simple_map Simple[]
	 */
	public function __construct(string $code = '', array $simple_map = [])
	{
		if ($code !== '') $this->code       = $code;
		if ($simple_map)  $this->simple_map = $simple_map;
	}

}
