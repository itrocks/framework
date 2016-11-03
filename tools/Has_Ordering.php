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
	 * @var integer
	 */
	public $ordering;

	//------------------------------------------------------------------------------------------ sort
	/**
	 * @param $objects_having_ordering object[]|self[]
	 * @return object[]|self[]
	 */
	public static function sort(array $objects_having_ordering)
	{
		uasort($objects_having_ordering, function ($c1, $c2) {
			/** @var $c1 Has_Ordering */
			/** @var $c2 Has_Ordering */
			return cmp($c1->ordering, $c2->ordering);
		});
		return $objects_having_ordering;
	}

}
