<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Widget\Data_List\Data_List_Exception;

/**
 * Word search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Range
{

	//------------------------------------------------------------------------------- MAX_RANGE_VALUE
	const MAX_RANGE_VALUE = 1;

	//------------------------------------------------------------------------------- MIN_RANGE_VALUE
	const MIN_RANGE_VALUE = -1;

	//----------------------------------------------------------------------------- NOT_A_RANGE_VALUE
	const NOT_A_RANGE_VALUE = 0;

	//------------------------------------------------------------------------------------ applyRange
	/**
	 * Apply a range expression on search string. The range is supposed to exist !
	 *
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @return Func\Range
	 * @throws Data_List_Exception
	 */
	public static function applyRange($search_value, Reflection_Property $property)
	{
		$range    = self::getRangeParts($search_value, $property);
		$range[0] = self::applyRangeValue($range[0], $property, self::MIN_RANGE_VALUE);
		$range[1] = self::applyRangeValue($range[1], $property, self::MAX_RANGE_VALUE);
		if ($range[0] === false || $range[1] === false) {
			throw new Data_List_Exception(
				$search_value, Loc::tr('Error in range expression or range must have 2 parts only')
			);
		}
		return self::buildRange($range[0], $range[1]);
	}

	//------------------------------------------------------------------------------- applyRangeValue
	/**
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @param $min_max      integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed
	 */
	protected static function applyRangeValue($search_value, Reflection_Property $property, $min_max)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				$search = Date::applyDateRangeValue($search_value, $min_max);
				break;
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default:
				$search = Scalar::applyScalar($search_value, $property, true);
				break;
		}
		return $search;
	}

	/**
	 * @param $min mixed
	 * @param $max mixed
	 * @return Func\Range
	 */
	public static function buildRange($min, $max)
	{
		return new Func\Range($min, $max);
	}

	//--------------------------------------------------------------------------------- getRangeParts
	/**
	 * Apply a range expression on search string. The range is supposed to exist !
	 *
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @return array
	 * @throws Data_List_Exception
	 */
	protected static function getRangeParts($search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				// Take care of char of formulas on expr like 'm-3-m', '01/m-2/2015-01/m-2/2016'...
				// pattern of a date that may contain formula
				$pattern = Date::getDateSubPattern();
				// We should analyse 1st the right pattern to solve cases like 1/5/y-1/7/y
				// We should parse like min=1/5/y and max=1/7/y
				// and not parse like min=1/5/y-1 and max=/7/y
				$pattern_right = "/[-](\\s* $pattern \\s* )$/x";
				$found = preg_match($pattern_right, $search_value, $matches);
				if ($found) {
					$max = trim($matches[1]);
					$min = trim(substr($search_value, 0, -(strlen($matches[0]))));
					// We check that left part is a date expression
					if (Date::isASingleDateFormula($min)) {
						$range = [$min, $max];
					}
					else {
						throw new Data_List_Exception(
							$search_value, Loc::tr('Error in left part of range expression')
						);
					}
				}
				else {
					throw new Data_List_Exception(
						$search_value, Loc::tr('Error in range expression or range must have 2 parts only')
					);
				}
				break;
			// Float | Integer | String types
			// case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default:
				$range = explode('-', $search_value, 2);
				// Check we have only two parts in the range!
				if (implode('-', $range) !== $search_value) {
					throw new Data_List_Exception($search_value, Loc::tr('Range must have 2 parts only'));
				}
				break;
		}
		return $range;
	}

	//-------------------------------------------------------------------------------------- hasRange
	/**
	 * Checks if a property has right to have range in search string
	 *
	 * @param $property Reflection_Property
	 * @return boolean true if range supported and authorized
	 */
	public static function supportsRange(Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		return ($property->getAnnotation('search_range')->value !== false)
		&& in_array($type_string, [Date_Time::class, Type::FLOAT, Type::INTEGER, Type::STRING]);
	}

	//--------------------------------------------------------------------------------------- isRange
	/**
	 * Check if expression is a range expression
	 *
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return boolean
	 */
	public static function isRange($search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class: {
				if (
					is_string($search_value)
					// take care of formula that may contains char '-'
					&& !Date::isASingleDateFormula($search_value)
					&& (strpos($search_value, '-') !== false)
				) {
					return true;
				}
				break;
			}
			default: {
				if (is_string($search_value) && (strpos($search_value, '-') !== false)) {
					return true;
				}
				break;
			}
		}
		return false;
	}


}
