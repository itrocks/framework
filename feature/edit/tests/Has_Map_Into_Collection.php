<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;

/**
 * An object that has a collection
 */
#[Store]
class Has_Map_Into_Collection
{

	//--------------------------------------------------------------------------- $collection_has_map
	/**
	 * @var Component_Has_Map[]
	 */
	#[Component]
	public array $collection_has_map;

}
