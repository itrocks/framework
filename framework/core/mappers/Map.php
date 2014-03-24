<?php
namespace SAF\Framework;

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
	 *
	 * @param $objects object[]
	 */
	public function __construct(&$objects = [])
	{
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
	 * @param $sort    Dao_Sort_Option
	 * @return object[] the sorted objects collection
	 *
	 * @todo Dao_Sort_Option should become something as simple as Sort, used by Dao and Collection
	 */
	public function sort(Dao_Sort_Option $sort = null)
	{
		if ($this->objects) {
			$object = reset($this->objects);
			if (!isset($sort)) {
				$sort = ($object instanceof List_Row)
					? new Dao_Sort_Option($object->getClassName())
					: new Dao_Sort_Option(get_class($object));
			}
			uasort($this->objects, function($object1, $object2) use ($sort)
				{
					if (($object1 instanceof List_Row) && ($object2 instanceof List_Row)) {
						$object1 = $object1->getObject();
						$object2 = $object2->getObject();
					}
					foreach ($sort->columns as $sort_column) {
						$reverse = isset($sort->reverse[$sort_column]);
						while (($i = strpos($sort_column, DOT)) !== false) {
							$column = substr($sort_column, 0, $i);
							$object1 = $object1->$column;
							$object2 = $object2->$column;
							$sort_column = substr($sort_column, $i + 1);
						}
						$value1 = $object1->$sort_column;
						$value2 = $object2->$sort_column;
						$compare = $reverse ? -strnatcasecmp($value1, $value2) : strnatcasecmp($value1, $value2);
						if ($compare) return $compare;
					}
					return 0;
				});
		}
		return $this->objects;
	}

}
