<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Feature\List_\Exception;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Word search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Range
{

	//------------------------------------------------------------------------------------------- MAX
	const MAX = 1;

	//------------------------------------------------------------------------------------------- MIN
	const MIN = -1;

	//------------------------------------------------------------------------------------------ NONE
	const NONE = 0;

	//------------------------------------------------------------------------------------ applyRange
	/**
	 * Apply a range expression on search string. The range is supposed to exist !
	 *
	 * @param $expression string|Option
	 * @param $property   Reflection_Property
	 * @return Func\Range
	 * @throws Exception
	 */
	public static function applyRange($expression, Reflection_Property $property)
	{
		$range    = explode('-', $expression, 2);
		$range[0] = self::applyRangeValue($range[0], $property, self::MIN);
		$range[1] = self::applyRangeValue($range[1], $property, self::MAX);
		if ($range[0] === false || $range[1] === false) {
			throw new Exception(
				$expression, Loc::tr('Error in range expression or range must have 2 parts only')
			);
		}
		return self::buildRange($range[0], $range[1]);
	}

	//------------------------------------------------------------------------------- applyRangeValue
	/**
	 * @param $expression string|Option
	 * @param $property   Reflection_Property
	 * @param $range_side integer  Range::MIN | Range::MAX | Range::NONE
	 * @return mixed
	 * @throws Exception
	 */
	protected static function applyRangeValue($expression, Reflection_Property $property,	$range_side)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				$search = Date::applyDateRangeValue($expression, $property, $range_side);
				break;
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default:
				$search = Scalar::applyScalar($expression, true);
				break;
		}
		return $search;
	}

	//------------------------------------------------------------------------------------ buildRange
	/**
	 * @param $min mixed
	 * @param $max mixed
	 * @return Func\Range
	 */
	public static function buildRange($min, $max)
	{
		return new Func\Range($min, $max);
	}

	//--------------------------------------------------------------------------------------- isRange
	/**
	 * Check if expression is a range expression
	 *
	 * @param $expression string
	 * @param $property   Reflection_Property
	 * @return boolean
	 */
	public static function isRange($expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class: {
				$is_date_expression = Date::isSingleDateExpression($expression);
				if (
					is_string($expression)
					// take care of formula that may contains char '-'
					&& !$is_date_expression
					&& (strpos($expression, '-') !== false)
				) {
					return true;
				}
				break;
			}
			default: {
				if (is_string($expression) && (strpos($expression, '-') !== false)) {
					return true;
				}
				break;
			}
		}
		return false;
	}

	//--------------------------------------------------------------------------------- supportsRange
	/**
	 * Checks if a property has right to have range in search string
	 *
	 * @param $property Reflection_Property
	 * @return boolean true if range supported and authorized
	 */
	public static function supportsRange(Reflection_Property $property)
	{
		$type_string  = $property->getType()->asString();
		$search_range = $property->getAnnotation('search_range')->value;
		$search_range = isset($search_range)
			? (new Boolean_Annotation($search_range))->value
			: ($property->getType()->isNumeric() || $property->getType()->isDateTime());
		return ($search_range !== false)
			&& in_array($type_string, [Date_Time::class, Type::FLOAT, Type::INTEGER, Type::STRING])
			// TODO NORMAL search range with @values crashes now, but it could be done
			&& !Values_Annotation::of($property)->value;
	}

}
