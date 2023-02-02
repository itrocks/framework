<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * An object that has map
 */
#[Store]
class Has_Map
{

	//----------------------------------------------------------------------------------- $simple_map
	/**
	 * @var Simple[]
	 */
	public array $simple_map;

}
