<?php
namespace ITRocks\Framework\Mapper;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A remover class implements a remove() object to remove any of its possible composite objects
 */
#[Store]
trait Remover
{

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Default remover removes an object from all collections properties of the object
	 *
	 * @param $object object contained object to remove
	 * @return integer removed instances count
	 */
	public function remove(object $object) : int
	{
		return Remover_Tool::removeObjectFromComposite($this, $object);
	}

}
