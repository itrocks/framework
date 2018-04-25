<?php
namespace ITRocks\Framework\Tools;

/**
 * Use it for any class or trait that need to have $ordering, helpful to sort elements
 *
 * @business
 * @group _top ordering
 * @sort ordering
 */
trait Has_Ordering
{

	//------------------------------------------------------------------------------------- $ordering
	/**
	 * @customized
	 * @empty_check false
	 * @user hide_output
	 * @var integer
	 */
	public $ordering;

	//--------------------------------------------------------------------------------------- reorder
	/**
	 * Reset the value of $ordering into the objects. Follows the natural sort order of the array
	 *
	 * @param $objects_having_ordering object[]|self[]
	 * @return object[]|self[]
	 */
	public static function reorder(array $objects_having_ordering)
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
	 * @param $objects_having_ordering object[]|self[]
	 * @return object[]|self[]
	 */
	public static function sort(array $objects_having_ordering)
	{
		uasort($objects_having_ordering, function (Has_Ordering $object1, Has_Ordering $object2) {
			return cmp($object1->ordering, $object2->ordering);
		});
		return $objects_having_ordering;
	}

}
