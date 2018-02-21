<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

/**
 * Wildcard search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Scalar
{

	//----------------------------------------------------------------------------------- applyScalar
	/**
	 * @param $search_value   string
	 * @param $is_range_value boolean true if we parse a range value
	 * @return string
	 */
	public static function applyScalar($search_value, $is_range_value = false)
	{
		return Wildcard::applyWildcards($search_value, $is_range_value);
	}

}
