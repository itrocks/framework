<?php
namespace ITRocks\Framework\Dao\Mysql\Column_Builder_Property;

/**
 * MySQL constant library
 */
class Integer
{

	//-------------------------------------------------------------------------------- MAXIMUM_LENGTH
	const MAXIMUM_LENGTH = 18;

	//--------------------------------------------------------------------------------------- MINIMUM
	const MINIMUM = -9223372036854775808;

	//-------------------------------------------------------------------------------- SIGNED_MAXIMUM
	const SIGNED_MAXIMUM = 9223372036854775807;

	//--------------------------------------------------------------------------------- SIGNED_RANGES
	const SIGNED_RANGES = [
		'tinyint'   => [3,         -128,        127],
		'smallint'  => [5,       -32768,      32767],
		'mediumint' => [7,     -8388608,    8388607],
		'int'       => [10, -2147483648, 2147483647],
		'bigint'    => [18, self::MINIMUM, self::SIGNED_MAXIMUM]
	];

	//------------------------------------------------------------------------------ UNSIGNED_MAXIMUM
	const UNSIGNED_MAXIMUM = 18446744073709551615;

	//------------------------------------------------------------------------------- UNSIGNED_RANGES
	const UNSIGNED_RANGES = [
		'tinyint'   => [3,  0,        255],
		'smallint'  => [5,  0,      65535],
		'mediumint' => [8,  0,   16777215],
		'int'       => [10, 0, 4294967295],
		'bigint'    => [18, 0, self::UNSIGNED_MAXIMUM]
	];

	//------------------------------------------------------------------------------------------ type
	/**
	 * @param $max_length integer
	 * @param $min_value  integer
	 * @param $max_value  integer
	 * @param $signed     boolean
	 * @return string
	 */
	public function type($max_length, $min_value, $max_value, $signed)
	{
		if ($min_value < 0) {
			$signed = true;
		}
		$ranges = $signed ? static::SIGNED_RANGES : static::UNSIGNED_RANGES;
		$range  = end($ranges);
		$type   = key($ranges);
		if (isset($max_length) || isset($min_value) || isset($max_value)) {
			foreach ($ranges as $type => $range) {
				if (
					(($max_length <= $range[0]) || !isset($max_length))
					&& (($min_value >= $range[1]) || !isset($min_value))
					&& (($max_value <= $range[2]) || !isset($max_value))
				) {
					break;
				}
			}
		}
		$type .= '(' . $range[0] . ')';
		if (!$signed) {
			$type .= ' unsigned';
		}
		return $type;
	}

}
