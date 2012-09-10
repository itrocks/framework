<?php
namespace SAF\Framework;

abstract class Identifier_Map_Data_Link extends Data_Link
{

	//----------------------------------------------------------------------------------------- clear
	/**
	 * clear() can't be done with current implementation, as each id is stored into the object itself
	 */
	public function clear() {}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Used to get an object's identifier
	 *
	 * A null value will be returned for an object that is not linked to data link. 
	 *
	 * @param object $object
	 * @return mixed
	 */
	public function getObjectIdentifier($object)
	{
		return $object->id;
	}

	//------------------------------------------------------------------------ removeObjectIdentifier
	/**
	 * Remove object identifier from object
	 *
	 * After this call, object will no longer be linked to data link.
	 * You can use this to clone objects into data links.
	 * This must be called when an object is deleted from data link, too. 
	 *
	 * @param object $object
	 */
	protected function removeObjectIdentifier($object)
	{
		unset($object->id);
	}

	//--------------------------------------------------------------------------------------- replace
	public function replace($destination, $source)
	{
		$this->setObjectIdentifier($destination, $this->getObjectIdentifier($source));
		$this->write($destination);
		return $destination;
	}

	//--------------------------------------------------------------------------- setObjectIdentifier
	/**
	 * Forces an object identifier
	 *
	 * Use it after an object is read from data link to associate it's identifier to it.
	 *
	 * @param object $object
	 * @param mixed  $id
	 * @return Identifier_Map_Data_Link
	 */
	protected function setObjectIdentifier($object, $id)
	{
		$object->id = $id;
		return $this;
	}

}
