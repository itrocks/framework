<?php
namespace SAF\Framework\Dao\Data_Link;

use SAF\Framework\Dao\Data_Link;
use SAF\Framework\Reflection\Reflection_Property_Value;

/**
 * Source of data link classes that use a map between internal identifiers and business objects
 */
abstract class Identifier_Map extends Data_Link
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
	 * @param $object        object an object to get data link identifier from
	 * @param $property_name string a property name to get data link identifier from instead of object
	 * @return mixed you can test if an object identifier is set with empty($of_this_result)
	 */
	public function getObjectIdentifier($object, $property_name = null)
	{
		if (is_object($object)) {
			if (isset($property_name)) {
				$id_property_name = ($property_name == 'id') ? 'id' : ('id_' . $property_name);
				if (isset($object->$id_property_name)) {
					return $object->$id_property_name;
				}
				else {
					return isset($object->$property_name)
						? (
							is_object($object->$property_name)
							? $this->getObjectIdentifier($object->$property_name)
							: $object->$property_name
						)
						: null;
				}
			}
			else {
				return isset($object->id) ? $object->id : null;
			}
		}
		else {
			return $object;
		}
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 object
	 * @param $object2 object
	 * @return boolean
	 */
	public function is($object1, $object2)
	{
		return $this->getObjectIdentifier($object1)
			&& ($this->getObjectIdentifier($object1) === $this->getObjectIdentifier($object2));
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
			$object = isset($id) ? ['id' => $id] : get_object_vars($object);
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
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return object the resulting $destination object
	 * @see Data_Link::replace()
	 */
	public function replace($destination, $source, $write = true)
	{
		$this->setObjectIdentifier($destination, $this->getObjectIdentifier($source));
		if ($write) {
			$this->write($destination);
		}
		return $destination;
	}

	//--------------------------------------------------------------------------- setObjectIdentifier
	/**
	 * Forces an object identifier
	 *
	 * Use it after an object is read from data link to associate it's identifier to it.
	 *
	 * @param $object        object
	 * @param $id            mixed
	 * @param $property_name string
	 * @return Identifier_Map
	 */
	protected function setObjectIdentifier($object, $id, $property_name = null)
	{
		// classic class object id
		if ($property_name) {
			$id_property_name = 'id_' . $property_name;
			$object->$id_property_name = $id;
		}
		else {
			// link class identifiers
			if (strpos($id, ',')) {
				foreach (explode(',', $id) as $property) {
					list($property_name, $id) = explode('=', $property);
					$id_property_name = 'id_' . $property_name;
					$object->$id_property_name = $id;
				}
			}
			// classic class id
			else {
				$object->id = $id;
			}
		}
		return $this;
	}

}
