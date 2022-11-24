<?php
namespace ITRocks\Framework\Dao\Data_Link;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Data_Link;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Annotation\Property\Link_Annotation;
use ITRocks\Framework\Reflection\Annotation\Property\Store_Annotation;
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
	protected function clear() : void
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
	public function disconnect(object $object, bool $load_linked_objects = false) : void
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
				if (Store_Annotation::of($property)->isJson() && is_string($value)) {
					$value = Getter::getLink($object, $property_name);
				}
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
				/** @noinspection PhpExpressionResultUnusedInspection need to call @getter */
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
	 * @param $object        ?object an object to get data link identifier from
	 * @param $property_name string|null property name to get data link identifier instead of object
	 * @return mixed you can test if an object identifier is set with empty($of_this_result)
	 */
	public function getObjectIdentifier(?object $object, string $property_name = null) : mixed
	{
		if (is_object($object)) {
			if (isset($property_name)) {
				$id_property_name = ($property_name === 'id') ? 'id' : ('id_' . $property_name);
				if (isset($object->$id_property_name)) {
					return $object->$id_property_name;
				}
				if (isset($object->$property_name)) {
					return is_object($object->$property_name)
						? $this->getObjectIdentifier($object->$property_name)
						: $object->$property_name;
				}
				return null;
			}
			return property_exists($object, 'id') ? $object->id : null;
		}
		return $object;
	}

	//-------------------------------------------------------------------------------------------- is
	/**
	 * Returns true if object1 and object2 match the same stored object
	 *
	 * @param $object1 ?object
	 * @param $object2 ?object
	 * @param $strict  boolean if true, will consider @link object and non-@link object different
	 * @return boolean
	 */
	public function is(?object $object1, ?object $object2, bool $strict = false) : bool
	{
		if (!isset($object1) && !isset($object2)) {
			return true;
		}
		return $this->getObjectIdentifier($object1)
			&& (
				strval($this->getObjectIdentifier($object1, $strict ? null : 'id'))
				=== strval($this->getObjectIdentifier($object2, $strict ? null : 'id'))
			)
			&& (
				is_a($object1, Builder::current()->sourceClassName(get_class($object2)))
				|| is_a($object2, Builder::current()->sourceClassName(get_class($object1)))
			);
	}

	//--------------------------------------------------------------------------------------- replace
	/**
	 * @param $destination T destination object
	 * @param $source      T source object
	 * @param $write       boolean true if the destination object must be immediately written
	 * @return T the resulting $destination object
	 * @see Data_Link::replace()
	 * @template T
	 */
	public function replace(object $destination, object $source, bool $write = true) : object
	{
		$identifier = $this->getObjectIdentifier($source);
		$this->setObjectIdentifier($destination, $identifier);
		if (!isStrictNumeric($identifier, false, false)) {
			$this->setObjectIdentifier($destination, $this->getObjectIdentifier($source, 'id'));
		}
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
	 * @param $property_name string|null
	 * @return $this
	 */
	public function setObjectIdentifier(object $object, mixed $id, string $property_name = null)
		: static
	{
		// classic class object id
		if ($property_name) {
			$id_property_name          = 'id_' . $property_name;
			$object->$id_property_name = $id;
		}
		else {
			// link class identifiers
			if ($id && str_contains($id, Link_Class::ID_SEPARATOR)) {
				foreach (explode(Link_Class::ID_SEPARATOR, $id) as $property) {
					[$property_name, $id] = explode('=', $property);
					if (is_numeric($id)) {
						$id_property_name          = 'id_' . $property_name;
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
