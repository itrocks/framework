<?php
namespace ITRocks\Framework\Dao\Mysql\Column_Builder_Property;

/**
 * MySQL constant library
 */
class Integer
{

	//--------------------------------------------------------------------- MySQL type name constants
	const BIG    = 'bigint';
	const MEDIUM = 'mediumint';
	const NORMAL = 'int';
	const SMALL  = 'smallint';
	const TINY   = 'tinyint';

	//-------------------------------------------------------------------------------- MAXIMUM_LENGTH
	const MAXIMUM_LENGTH = 18;

	//--------------------------------------------------------------------------------------- MINIMUM
	const MINIMUM = -9223372036854775808;

	//-------------------------------------------------------------------------------- SIGNED_MAXIMUM
	const SIGNED_MAXIMUM = 9223372036854775807;

	//--------------------------------------------------------------------------------- SIGNED_RANGES
	/**
	 * Signed ranges are sorted from the smallest to the biggest
	 * (sorting makes sense : do not change it !)
	 *
	 * $mysql_type => [$mysql_size, $mysql_minimal_value, $mysql_maximal_value]
	 */
	const SIGNED_RANGES = [
		self::TINY   => [3,         -128,        127],
		self::SMALL  => [5,       -32768,      32767],
		self::MEDIUM => [7,     -8388608,    8388607],
		self::NORMAL => [10, -2147483648, 2147483647],
		self::BIG    => [18, self::MINIMUM, self::SIGNED_MAXIMUM]
	];

	//------------------------------------------------------------------------------ UNSIGNED_MAXIMUM
	const UNSIGNED_MAXIMUM = 18446744073709551615;

	//------------------------------------------------------------------------------- UNSIGNED_RANGES
	/**
	 * Unsigned ranges are sorted from the smallest to the biggest
	 * (sorting makes sense : do not change it !)
	 *
	 * $mysql_type => [$mysql_size, $mysql_minimal_value, $mysql_maximal_value]
	 */
	const UNSIGNED_RANGES = [
		self::TINY   => [3,  0,        255],
		self::SMALL  => [5,  0,      65535],
		self::MEDIUM => [8,  0,   16777215],
		self::NORMAL => [10, 0, 4294967295],
		self::BIG    => [18, 0, self::UNSIGNED_MAXIMUM]
	];

	//------------------------------------------------------------------------------------------ type
	/**
	 * @param $max_length ?int
	 * @param $min_value  ?int
	 * @param $max_value  ?int
	 * @param $signed     ?boolean
	 * @return string
	 */
	public function type(?int $max_length, ?int $min_value, ?int $max_value, ?bool $signed) : string
	{
		if ($min_value < 0) {
			$signed = true;
		}
		$ranges = $signed ? static::SIGNED_RANGES : static::UNSIGNED_RANGES;
		$range  = $ranges[self::BIG];
		$type   = self::BIG;
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
