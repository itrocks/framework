<?php
namespace ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Widget\Data_List\Data_List_Exception;

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
	 * @throws Data_List_Exception
	 */
	public static function applyComparison($expression, Reflection_Property $property)
	{

		$comparison    = self::getComparisonParts($expression, $property);
		$comparison[1] = self::applyComparisonValue($comparison[0], $comparison[1], $property);
		return self::buildComparison($comparison[0], $comparison[1]);
	}

	//-------------------------------------------------------------------------- applyComparisonValue
	/**
	 * @param $sign string
	 * @param $expression string|Option
	 * @param $property     Reflection_Property
	 * @return string
	 */
	protected static function applyComparisonValue($sign, $expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				if (($sign == '<') || ($sign == '>=')) {
					$search = Date::applyDateRangeValue($expression, self::MIN);
				}
				else {
					$search = Date::applyDateRangeValue($expression, self::MAX);
				}
				break;
			default:
				$search = Scalar::applyScalar($expression, $property, true);
				break;
		}
		return $search;
	}

	//------------------------------------------------------------------------------- buildComparison
	/**
	 * @param $sign string
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
	 * @throws Data_List_Exception
	 */
	protected static function getComparisonParts($expression, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class:
				if (!Date::isASingleDateExpression($expression)) {
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
							$comparison = [$sign, $comparison];
					}
					else {
						throw new Data_List_Exception(
							$expression, Loc::tr('Error in left part of comparison expression')
						);
					}
					}
					else {
						throw new Data_List_Exception(
							$expression, Loc::tr('Error in comparison expression')
						);
					}
				break;
			default:
				if (strstr($expression, '<')) {
					if (strstr($expression, '<=')) {
						$comparison    = explode('<=', $expression, 2);
						$comparison[0] = '<=';
					}
					else {
						$comparison    = explode('<', $expression, 2);
						$comparison[0] = '<';
					}
				}
				else {
					if (strstr($expression, '>=')) {
						$comparison    = explode('>=', $expression, 2);
						$comparison[0] = '>=';
					}
					else {
						$comparison    = explode('>', $expression, 2);
						$comparison[0] = '>';
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
				$is_date_expression = Date::isASingleDateExpression($expression);
				if (
					is_string($expression)
					&& !$is_date_expression
					&& ((strstr($expression, '<')) || (strstr($expression, '>')))
				) {
					return true;
				}
				break;
			}
			default: {
				if (is_string($expression)
					&& ((strstr($expression, '<')) || (strstr($expression, '>')))
				) {
					return true;
				}
				break;
			}
		}
		return false;
	}

}
