<?php
namespace SAF\Framework;

trait Remover
{

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Default remover removes an object from all collections properties of the object
	 *
	 * @param $object object contained object to remove
	 * @return integer removed instances count
	 */
	public function remove($object)
	{
		return Remover_Tool::removeObjectFromComposite($this, $object);
	}

}
