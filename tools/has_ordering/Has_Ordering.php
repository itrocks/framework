<?php
namespace ITRocks\Framework\Tools;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * Use it for any class or trait that need to have $ordering, helpful to sort elements
 *
 * @sort ordering
 */
#[Store]
trait Has_Ordering
{

	//------------------------------------------------------------------------------------- $ordering
	/**
	 * @customized
	 * @empty_check false
	 * @no_autowidth
	 * @user hide_output
	 * @var integer
	 */
	public int $ordering;

	//--------------------------------------------------------------------------------------- reorder
	/**
	 * Reset the value of $ordering into the objects. Follows the natural sort order of the array
	 *
	 * @param $objects_having_ordering object[]|self[]
	 * @return object[]|self[]
	 */
	public static function reorder(array $objects_having_ordering) : array
	{
		$ordering = 0;
		foreach ($objects_having_ordering as $object) {
			$object->ordering = ++$ordering;
		}
		return $objects_having_ordering;
	}

	//------------------------------------------------------------------------------------------ sort
	/**
	 * Sort objects by their value of $ordering
	 *
	 * @param $objects_having_ordering static[]
	 * @return static[]
	 */
	public static function sort(array $objects_having_ordering) : array
	{
		uasort($objects_having_ordering, function (object $object1, object $object2) : int {
			return cmp($object1->ordering, $object2->ordering);
		});
		return $objects_having_ordering;
	}

}
