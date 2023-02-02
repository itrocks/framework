<?php
namespace ITRocks\Framework\Feature\Edit\Tests;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;

/**
 * An object that has a collection
 */
#[Store]
class Has_Collection
{

	//---------------------------------------------------------------------------- $simple_collection
	/**
	 * @var Simple_Component[]
	 */
	#[Component]
	public array $simple_collection;

}
