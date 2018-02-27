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
	 * @param $max_length integer
	 * @param $max_value  integer
	 * @param $signed     boolean
	 * @param $precision  integer
	 * @return string
	 */
	public function type($max_length, $max_value, $signed, $precision)
	{
		if (!isset($max_length)) {
			$max_length = isset($max_value) ? strlen($max_value) : (self::MAX_LENGTH - $precision);
		}
		$type = 'decimal(' . $max_length . ',' . $precision . ')';
		if ($signed) {
			$type .= ' unsigned';
		}
		return $type;
	}

}
