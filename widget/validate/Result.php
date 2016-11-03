<?php
namespace ITRocks\Framework\Widget\Validate;

/**
 * Validate result constants
 */
abstract class Result
{

	//----------------------------------------------------------------------------------------- ERROR
	const ERROR = 'error';

	//----------------------------------------------------------------------------------- INFORMATION
	const INFORMATION = 'information';

	//------------------------------------------------------------------------------------------ NONE
	const NONE = null;

	//----------------------------------------------------------------------------------------- VALID
	const VALID = true;

	//--------------------------------------------------------------------------------------- WARNING
	const WARNING = 'warning';

	//------------------------------------------------------------------------------------- andResult
	/**
	 * @param $result     string|null|true
	 * @param $and_result string|null|true
	 * @return string|null|true
	 */
	public static function andResult($result, $and_result)
	{
		$levels = [true, null, self::INFORMATION, self::WARNING, self::ERROR];
		$result_level     = array_search($result, $levels, true);
		$and_result_level = array_search($and_result, $levels, true);
		return ($result_level > $and_result_level) ? $result : $and_result;
	}

	//--------------------------------------------------------------------------------------- isValid
	/**
	 * @param $result           string @values self::const
	 * @param $warning_is_valid boolean
	 * @return boolean
	 */
	public static function isValid($result, $warning_is_valid = false)
	{
		return $warning_is_valid
			? ($result !== self::ERROR)
			: !in_array($result, [self::ERROR, self::WARNING], true);
	}

}
