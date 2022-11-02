<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

/**
 * Wildcard search parameters parser
 */
abstract class Scalar
{

	//----------------------------------------------------------------------------------- applyScalar
	/**
	 * @param $search_value   string
	 * @param $is_range_value boolean true if we parse a range value
	 * @return string
	 */
	public static function applyScalar(string $search_value, bool $is_range_value = false) : string
	{
		return Wildcard::applyWildcards($search_value, $is_range_value);
	}

}
