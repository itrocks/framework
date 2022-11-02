<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Traits\Has_Code;

/**
 * A simple test object
 */
class Component_Has_Map
{
	use Component;
	use Has_Code;

	//------------------------------------------------------------------------------------ $composite
	/**
	 * @composite
	 * @link Object
	 * @var Has_Collection
	 */
	public Has_Collection $composite;

	//----------------------------------------------------------------------------------- $simple_map
	/**
	 * @link Map
	 * @var Simple[]
	 */
	public array $simple_map;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * constructor
	 *
	 * @param $code       string|null
	 * @param $simple_map Simple[]|null
	 */
	public function __construct(string $code = null, array $simple_map = null)
	{
		if (isset($code))       $this->code       = $code;
		if (isset($simple_map)) $this->simple_map = $simple_map;
	}

}
