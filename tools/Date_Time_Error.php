<?php
namespace ITRocks\Framework\Tools;

/**
 * A date-time error contains an empty date-time and what was the original stored date-time we
 * could not convert to Date_Time
 *
 * Do never call this directly without knowing what you are doing :
 * - only fromError() should be called
 */
class Date_Time_Error extends Date_Time
{

	//---------------------------------------------------------------------------------------- $error
	/**
	 * @var string
	 */
	public $error;

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string The error entry
	 */
	public function __toString()
	{
		return $this->error;
	}

	//------------------------------------------------------------------------------------- fromError
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $date string
	 * @return Date_Time equals Date_Time::min()
	 */
	public static function fromError($date)
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date_time        = new Date_Time_Error(self::$min_date);
		$date_time->error = $date;
		return $date_time;
	}

}
