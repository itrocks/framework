<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
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
	 * @param $is_range_value boolean  true if we parse a range value
	 * @return string
	 */
	public static function applyScalar(
		$search_value, Reflection_Property $property, $is_range_value = false
	) {
		// check if we are on a enum field with @values list of values
		$values = $property->getListAnnotation('values')->values();
		if (count($values)) {
			//we do not apply wildcards, we want search for this exact value
			return Func::equal($search_value);
		}
		return Wildcard::applyWildcards($search_value, $is_range_value);
	}


}
