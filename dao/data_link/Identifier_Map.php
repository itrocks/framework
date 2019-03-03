<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Widget_Annotation;
use ITRocks\Framework\Reflection\Link_Class;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Widget\Collection_As_Map;

/**
 * Source of data link classes that use a map between internal identifiers and business objects
 */
abstract class Identifier_Map extends Data_Link
{

	//----------------------------------------------------------------------------------------- clear
	/**
	 * clear() can't be done with current implementation, as each id is stored into the object itself
	 */
	protected function clear()
	{}

	//------------------------------------------------------------------------------------ disconnect
	/**
	 * Disconnect an object from current data link
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object object to disconnect from data source
	 * @param $load_linked_objects boolean if true, load linked objects before disconnect
	 * @see Data_Link::disconnect()
	 */
	public function disconnect($object, $load_linked_objects = false)
	{
		// disconnect component objects, including collection elements
		/** @noinspection PhpUnhandledExceptionInspection $object is an object */
		foreach ((new Reflection_Class($object))->getProperties([T_EXTENDS, T_USE]) as $property) {
			$property_name   = $property->name;
			$link_annotation = Link_Annotation::of($property);
			if (
				($link_annotation->isCollection() || $property->getAnnotation('component')->value)
				&& !empty($object->$property_name)
				&& !is_a(Widget_Annotation::of($property)->value, Collection_As_Map::class, true)
			) {
				$value = $object->$property_name;
				if (is_array($value)) {
					foreach ($value as $element) {
						$this->disconnect($element);
					}
				}
				else {
					$this->disconnect($value);
				}
			}
			elseif ($load_linked_objects && $link_annotation->isMap()) {
				$object->$property_name;
			}
		}
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
	 * @param $strict boolean if true, will consider @link object and non-@link object as different
	 * @return boolean
	 */
	public function is($object1, $object2, $strict = false)
	{
		$result = $this->getObjectIdentifier($object1)
			&& (
				strval($this->getObjectIdentifier($object1, $strict ? null : 'id'))
				=== strval($this->getObjectIdentifier($object2, $strict ? null : 'id'))
			)
			&& (
				is_a($object1, Builder::current()->sourceClassName(get_class($object2)))
				|| is_a($object2, Builder::current()->sourceClassName(get_class($object1)))
			);
		return $result;
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
	 * Use it after an object is read from data link to associate its identifier to it.
	 *
	 * @param $object        object
	 * @param $id            mixed
	 * @param $property_name string
	 * @return Identifier_Map
	 */
	public function setObjectIdentifier($object, $id, $property_name = null)
	{
		// classic class object id
		if ($property_name) {
			$id_property_name = 'id_' . $property_name;
			$object->$id_property_name = $id;
		}
		else {
			// link class identifiers
			if (strpos($id, Link_Class::ID_SEPARATOR)) {
				foreach (explode(Link_Class::ID_SEPARATOR, $id) as $property) {
					list($property_name, $id) = explode('=', $property);
					if (is_numeric($id)) {
						$id_property_name = 'id_' . $property_name;
						$object->$id_property_name = $id;
					}
					else {
						$object->$property_name = $id;
					}
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
