<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func\Comparison;

/**
 * Wildcard search parameters parser
 */
abstract class Scalar
{

	//----------------------------------------------------------------------------------- applyScalar
	/**
	 * @param $search_value   string
	 * @param $is_range_value boolean true if we parse a range value
	 * @return Comparison|string
	 */
	public static function applyScalar(string $search_value, bool $is_range_value = false)
		: Comparison|string
	{
		return Wildcard::applyWildcards($search_value, $is_range_value);
	}

}
