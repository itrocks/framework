<?php
namespace SAF\Framework;

/**
 * A collection is an array of objects that are a component of the container object
 *
 * This means that each object of a collection should not exist without it's container object
 */
class Collection
{

	//------------------------------------------------------------------------------------------- add
	/**
	 * Add an object into an objects array
	 *
	 * @param $array   array
	 * @param $element object|object[]
	 */
	public static function add(&$array, $element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				self::add($array, $elem);
			}
		}
		else {
			$array[Dao::getObjectIdentifier($element)] = $element;
		}
	}

	//------------------------------------------------------------------------------------------- has
	/**
	 * Returns true if the objects array has the object
	 *
	 * @param $array   array
	 * @param $element object
	 * @return boolean
	 */
	public static function has(&$array, $element)
	{
		$key = Dao::getObjectIdentifier($element);
		return array_key_exists($key, $array);
	}

	//---------------------------------------------------------------------------------------- remove
	/**
	 * Remove an object from an objects array
	 *
	 * @param $array   array
	 * @param $element object|object[]
	 */
	public static function remove(&$array, $element)
	{
		if (is_array($element)) {
			foreach ($element as $elem) {
				self::remove($array, $elem);
			}
		}
		else {
			$key = Dao::getObjectIdentifier($element);
			if (!array_key_exists($key, $array)) {
				$array[$key] = $element;
			}
		}
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Sorts a collection of objects
	 *
	 * @param $objects object[] the objects collection to sort
	 * @return object[] the sorted objects collection
	 */
	public static function sort($objects)
	{
		if ($objects) {
			// todo Dao_Sort_Option should become something as simple as Sort, used by Dao and Collection
			$object = reset($objects);
			$sort = ($object instanceof List_Row)
				? new Dao_Sort_Option($object->getClassName())
				: new Dao_Sort_Option(get_class($object));
			uasort($objects, function($object1, $object2) use ($sort)
			{
				if (($object1 instanceof List_Row) && ($object2 instanceof List_Row)) {
					$object1 = $object1->getObject();
					$object2 = $object2->getObject();
				}
				foreach ($sort->columns as $sort_column) {
					$reverse = isset($sort->reverse[$sort_column]);
					while (($i = strpos($sort_column, ".")) !== false) {
						$column = substr($sort_column, 0, $i);
						$object1 = $object1->$column;
						$object2 = $object2->$column;
						$sort_column = substr($sort_column, $i + 1);
					}
					$value1 = $object1->$sort_column;
					$value2 = $object2->$sort_column;
					$compare = $reverse ? -strcasecmp($value1, $value2) : strcasecmp($value1, $value2);
					if ($compare) return $compare;
				}
				return 0;
			});
		}
		return $objects;
	}

}
