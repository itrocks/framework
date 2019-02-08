<?php
namespace ITRocks\Framework\Feature\List_\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Feature\List_\Exception;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Word search parameters parser
 *
 * @extends Search_Parameter_Parser
 */
abstract class Comparison
{

	//------------------------------------------------------------------------------------------- MAX
	const MAX = 1;

	//------------------------------------------------------------------------------------------- MIN
	const MIN = -1;

	//------------------------------------------------------------------------------- applyComparison
	/**
	 * Apply a Comparison expression on search string. The Comparison is supposed to exist !
	 *
	 * @param $expression string|Option
	 * @param $property   Reflection_Property
	 * @return Func\Comparison
	 * @throws Exception
	 */
	public static function applyComparison($expression, Reflection_Property $property)
	{

		$comparison               = self::getComparisonParts($expression, $property);
		$comparison['than_value'] = self::applyComparisonValue(
			$comparison['sign'], $comparison['than_value'], $property
		);
		return self::buildComparison($comparison['sign'], $comparison['than_value']);
	}

	//-------------------------------------------------------------------------- applyComparisonValue
	/**
	 * @param $sign       string
	 * @param $expression string|Option
	 * @param $property   Reflection_Property
	 * @return string
	 * @throws Exception
	 */
	protected static function applyComparisonValue($sign, $expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				$search = Date::applyDateRangeValue(
					$expression, $property, in_array($sign, ['<', '>=']) ? self::MIN : self::MAX
				);
				break;
			default:
				$search = Scalar::applyScalar($expression, true);
				break;
		}
		return $search;
	}

	//------------------------------------------------------------------------------- buildComparison
	/**
	 * @param $sign       string
	 * @param $than_value mixed
	 * @return Func\Comparison
	 */
	public static function buildComparison($sign, $than_value)
	{
		return new Func\Comparison($sign, $than_value);
	}

	//---------------------------------------------------------------------------- getComparisonParts
	/**
	 * Apply a comparison expression on search string. The comparison is supposed to exist !
	 *
	 * @param $expression string|Option
	 * @param $property   Reflection_Property
	 * @return array
	 * @throws Exception
	 */
	protected static function getComparisonParts($expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				if (!Date::isSingleDateExpression($expression)) {
					$pattern = Date::getDatePattern(false);
					if (strstr($expression, '<')) {
						if (strstr($expression, '<=')) {
							$pattern_right = "/[<=](\\s* $pattern \\s* )$/x";
							$sign          = '<=';
						}
						else {
							$pattern_right = "/[<](\\s* $pattern \\s* )$/x";
							$sign          = '<';
						}
						$found = preg_match($pattern_right, $expression, $matches);
					}
					else {
						if (strstr($expression, '>=')) {
							$pattern_right = "/[>=](\\s* $pattern \\s* )$/x";
							$sign          = '>=';
						}
						else {
							$pattern_right = "/[>](\\s* $pattern \\s* )$/x";
							$sign          = '>';
						}
						$found = preg_match($pattern_right, $expression, $matches);
					}
					if ($found) {
							$comparison = trim($matches[1]);
							$comparison = ['sign' => $sign, 'than_value' => $comparison];
					}
					else {
						throw new Exception(
							$expression, Loc::tr('Error in left part of comparison expression')
						);
					}
				}
				else {
					throw new Exception(
						$expression, Loc::tr('Error in comparison expression')
					);
				}
				break;
			default:
				if (strstr($expression, '<')) {
					if (strstr($expression, '<=')) {
						$comparison['than_value'] = explode('<=', $expression, 2)[1];
						$comparison['sign']       = '<=';
					}
					else {
						$comparison['than_value'] = explode('<', $expression, 2)[1];
						$comparison['sign']       = '<';
					}
				}
				else {
					if (strstr($expression, '>=')) {
						$comparison['than_value'] = explode('>=', $expression, 2)[1];
						$comparison['sign']       = '>=';
					}
					else {
						$comparison['than_value'] = explode('>', $expression, 2)[1];
						$comparison['sign']       = '>';
					}
				}
				break;
		}
		return $comparison;
	}

	//---------------------------------------------------------------------------------- isComparison
	/**
	 * Check if expression is a compare expression
	 *
	 * @param $expression string
	 * @param $property   Reflection_Property
	 * @return boolean
	 */
	public static function isComparison($expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class: {
				$is_date_expression = Date::isSingleDateExpression($expression);
				if (
					is_string($expression)
					&& !$is_date_expression
					&& (strstr($expression, '<') || strstr($expression, '>'))
				) {
					return true;
				}
				break;
			}
			default: {
				if (is_string($expression) && (strstr($expression, '<') || strstr($expression, '>'))) {
					return true;
				}
				break;
			}
		}
		return false;
	}

}
