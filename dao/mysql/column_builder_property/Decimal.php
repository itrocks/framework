<?php
namespace ITRocks\Framework\Dao\Mysql\Column_Builder_Property;

/**
 * Decimal column builder
 */
class Decimal
{

	//------------------------------------------------------------------------------------ MAX_LENGTH
	const MAX_LENGTH = 65;

	//------------------------------------------------------------------------------------------ type
	/**
	 * @param $max_length ?integer in digits : counts the comma, and the sign if signed
	 * @param $max_value  ?float
	 * @param $min_value  ?float
	 * @param $signed     ?boolean
	 * @param $precision  ?integer
	 * @return string
	 */
	public function type(
		?int $max_length, ?float $min_value, ?float $max_value, ?bool $signed, ?int $precision
	) : string
	{
		// default : length of the decimal part of $min/max_value, or maximal available length
		if (!isset($max_length)) {
			if (isset($max_value)) {
				$max_length = strlen(ltrim(lParse($max_value, DOT), '-')) + $precision;
			}
			else {
				$max_length = self::MAX_LENGTH;
			}
			if (isset($min_value)) {
				$max_length = max($max_length, strlen(ltrim(lParse($min_value, DOT), '-')) + $precision);
			}
			elseif ($signed) {
				$max_length = self::MAX_LENGTH;
			}
			// counts the comma and the minus sign (if signed)
			$max_length += ($signed ? 2 : 1);
		}
		// it.rocks counts the comma and the minus sign size, MySQL does not !
		$mysql_length = $max_length - ($signed ? 2 : 1);
		$type         = 'decimal(' . $mysql_length . ',' . $precision . ')';
		if (!$signed) {
			$type .= ' unsigned';
		}
		return $type;
	}

}
