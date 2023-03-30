<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Exception;
use ITRocks\Framework\Reflection\Annotation\Template\Boolean_Annotation;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Word search parameters parser
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
	 * @param $expression string
	 * @param $property   ?Reflection_Property
	 * @return Func\Range
	 * @throws Exception
	 */
	public static function applyRange(string $expression, ?Reflection_Property $property)
		: Func\Range
	{
		if ($property && $property->getType()->isDateTime() && (substr_count($expression, '-') > 1)) {
			if (
				preg_match('/[a-z]\s*(-)\s*[a-z]/', $expression, $match, PREG_OFFSET_CAPTURE)
				|| preg_match('/[0-9]\s*(-)\s*[a-z]/', $expression, $match, PREG_OFFSET_CAPTURE)
			) {
				$split_position = $match[1][1];
			}
			else {
				$split_position = strpos($expression, '-');
			}
			$range = [substr($expression, 0, $split_position), substr($expression, $split_position + 1)];
		}
		else {
			$range = explode('-', $expression, 2);
		}
		$range[0] = self::applyRangeValue($range[0], $property, self::MIN);
		$range[1] = self::applyRangeValue($range[1], $property, self::MAX);
		return self::buildRange($range[0], $range[1]);
	}

	//------------------------------------------------------------------------------- applyRangeValue
	/**
	 * @param $expression string
	 * @param $property   ?Reflection_Property
	 * @param $range_side integer @values static::const
	 * @return Func\Comparison|Func\Logical|Func\Range|string
	 * @throws Exception
	 */
	protected static function applyRangeValue(
		string $expression, ?Reflection_Property $property, int $range_side
	) : Func\Comparison|Func\Logical|Func\Range|string
	{
		$type_string = $property?->getType()->asString();
		return ($type_string === Date_Time::class)
			? Date::applyDateRangeValue($expression, $property, $range_side)
			: Scalar::applyScalar($expression, true);
	}

	//------------------------------------------------------------------------------------ buildRange
	/**
	 * @param $min string
	 * @param $max string
	 * @return Func\Range
	 */
	public static function buildRange(string $min, string $max) : Func\Range
	{
		return new Func\Range($min, $max);
	}

	//--------------------------------------------------------------------------------------- isRange
	/**
	 * Check if expression is a range expression
	 *
	 * @param $expression string
	 * @param $property   ?Reflection_Property
	 * @return boolean
	 */
	public static function isRange(string $expression, ?Reflection_Property $property) : bool
	{
		return str_contains($expression, '-')
			&& !($property?->getType()->isDateTime() && Date::isSingleDateExpression($expression));
	}

	//--------------------------------------------------------------------------------- supportsRange
	/**
	 * Checks if a property has right to have range in search string
	 *
	 * @param $property ?Reflection_Property
	 * @return boolean true if range supported and authorized
	 */
	public static function supportsRange(?Reflection_Property $property) : bool
	{
		$type         = $property?->getType() ?: new Type(Type::STRING);
		$type_string  = $type->asString();
		$search_range = $property?->getAnnotation('search_range')->value;
		$search_range = isset($search_range)
			? (new Boolean_Annotation($search_range))->value
			: ($type->isNumeric() || $type->isDateTime());
		return ($search_range !== false)
			&& in_array($type_string, [Date_Time::class, Type::FLOAT, Type::INTEGER, Type::STRING])
			// TODO NORMAL search range with @values crashes now, but it could be done
			&& (!$property || !Values::of($property)?->values);
	}

}
