<?php

abstract class Identifier_Map_Data_Link implements Data_Link
{

	//----------------------------------------------------------------------------------------- clear
	public function clear()
	{
		// this can't be done with current implementation, as each id is stored into the object itself.
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	public function getObjectIdentifier($object)
	{
		return $object->id;
	}

	//------------------------------------------------------------------------ removeObjectIdentifier
	public function removeObjectIdentifier($object)
	{
		unset($object->id);
		return $this;
	}

	//--------------------------------------------------------------------------- setObjectIdentifier
	public function setObjectIdentifier($object, $id)
	{
		$object->id = $id;
		return $this;
	}

}
