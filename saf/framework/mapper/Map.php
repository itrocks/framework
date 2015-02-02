<?php
namespace SAF\Framework\Mapper;

use SAF\Framework\Dao;
use SAF\Framework\Dao\Option\Sort;
use SAF\Framework\Tools\List_Row;

/**
 * A map is an array of objects which the container object is linked to
 */
class Map
{

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * @var object[]
	 */
	public $objects;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * A collection of objects of the same class, linked to the same data link
	 * Beware : $objects array is used as reference and will be altered by any changes made to the map
	 *
	 * @param $objects   object[]
	 * @param $key_is_id boolean Set this to true if your objects array use objects id as key
	 *                           This will enable an optimization to get this working faster
	 */
	public function __construct(&$objects = [], $key_is_id = false)
	{
		if (!$key_is_id) {
			$this->objects = $objects;
			$objects       = [];
			foreach ($this->objects as $key => $object) {
				$objects[Dao::getObjectIdentifier($object) ?: $key] = $object;
			}
		}
		$this->objects =& $objects;
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an object into an objects array
	 *
	 * @param $element object|object[]
	 */
	public function add($element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				$this->objects[Dao::getObjectIdentifier($elem)] = $elem;
			}
		}
		else {
			$this->objects[Dao::getObjectIdentifier($element)] = $element;
		}
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the objects array has the object
	 *
	 * @param $element object
	 * @return boolean
	 */
	public function has($element)
	{
		$key = Dao::getObjectIdentifier($element);
		return isset($this->objects[$key]) || array_key_exists($key, $this->objects);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from an objects array
	 *
	 * @param $element object|object[]
	 */
	public function remove($element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				$key = Dao::getObjectIdentifier($elem);
				if (isset($this->objects[$key]) || array_key_exists($key, $this->objects)) {
					unset($this->objects[$key]);
				}
			}
		}
		else {
			$key = Dao::getObjectIdentifier($element);
			if (isset($this->objects[$key]) || array_key_exists($key, $this->objects)) {
				unset($this->objects[$key]);
			}
		}
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Sorts a collection of objects and returns the sorted objects collection
	 *
	 * @param $sort    Sort
	 * @return object[] the sorted objects collection
	 *
	 * @todo Dao_Sort_Option should become something as simple as Sort, used by Dao and Collection
	 */
	public function sort(Sort $sort = null)
	{
		if ($this->objects) {
			$object = reset($this->objects);
			if (!isset($sort)) {
				$sort = ($object instanceof List_Row)
					? new Sort($object->getClassName())
					: new Sort(get_class($object));
			}
			uasort($this->objects, function($object1, $object2) use ($sort)
			{
				if (($object1 instanceof List_Row) && ($object2 instanceof List_Row)) {
					$object1 = $object1->getObject();
					$object2 = $object2->getObject();
				}
				foreach ($sort->columns as $sort_column) {
					$reverse = isset($sort->reverse[strval($sort_column)]);
					while (($i = strpos($sort_column, DOT)) !== false) {
						$column = substr($sort_column, 0, $i);
						$object1 = isset($object1) ? $object1->$column : null;
						$object2 = isset($object2) ? $object2->$column : null;
						$sort_column = substr($sort_column, $i + 1);
					}
					$value1 = isset($object1) ? $object1->$sort_column : null;
					$value2 = isset($object2) ? $object2->$sort_column : null;
					$compare = $reverse ? -strnatcasecmp($value1, $value2) : strnatcasecmp($value1, $value2);
					if ($compare) return $compare;
				}
				return 0;
			});
		}
		return $this->objects;
	}

}
