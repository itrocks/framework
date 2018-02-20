<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Reflection\Reflection_Property;

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
	 * @param $property       Reflection_Property
	 * @param $is_range_value boolean true if we parse a range value
	 * @return string
	 */
	public static function applyScalar(
		$search_value,
		/** @noinspection PhpUnusedParameterInspection */ Reflection_Property $property,
		$is_range_value = false
	) {
		return Wildcard::applyWildcards($search_value, $is_range_value);
	}

}
