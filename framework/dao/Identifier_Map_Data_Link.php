<?php
namespace SAF\Framework;

/**
 * Source of data link classes that use a map between internal identifiers and business objects
 */
abstract class Identifier_Map_Data_Link extends Data_Link
{

	//----------------------------------------------------------------------------------------- clear
	/**
	 * clear() can't be done with current implementation, as each id is stored into the object itself
	 */
	protected function clear() {}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @param $object object object to disconnect from data source
	 * @see Data_Link::disconnect()
	 */
	public function disconnect($object)
	{
		if (isset($object->id)) {
			unset($object->id);
		}
	}

	//--------------------------------------------------------------------------- getObjectIdentifier
	/**
	 * Used to get an object's identifier
	 *
	 * A null value will be returned for an object that is not linked to data link.
	 * If $object is already an identifier, the identifier is returned.
	 *
	 * @param $object object an object to get data link identifier from
	 * @return mixed you can test if an object identifier is set with empty($of_this_result)
	 */
	public function getObjectIdentifier($object)
	{
		return is_object($object) ? (isset($object->id) ? $object->id : null) : $object;
	}

	//---------------------------------------------------------------------------- objectToProperties
	/**
	 * Changes an object into an array associating properties and their values
	 *
	 * @param $object array|object|null if already an array, nothing will be done
	 * @return mixed[] indices ar properties paths
	 */
	protected function objectToProperties($object)
	{
		if (is_object($object)) {
			$id = $this->getObjectIdentifier($object);
			$object = isset($id) ? array("id" => $id) : get_object_vars($object);
		}
		elseif (is_array($object)) {
			foreach ($object as $path => $value) {
				if ($value instanceof Reflection_Property_Value) {
					$object[$path] = $value->value();
				}
			}
		}
		return $object;
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $destination object destination object
	 * @param $source      object source object
	 * @return object the resulting $destination object
	 * @see Data_Link::replace()
	 */
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
	 * @param $object object
	 * @param $id mixed
	 * @return Identifier_Map_Data_Link
	 */
	protected function setObjectIdentifier($object, $id)
	{
		$object->id = $id;
		return $this;
	}

}
