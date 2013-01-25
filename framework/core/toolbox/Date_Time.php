<?php
namespace SAF\Framework;
use DateTime;
use DateTimeZone;

class Date_Time extends DateTime
{

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return $this->toISO();
	}

	//------------------------------------------------------------------------------ createFromFormat
	/**
	 * @param $format string
	 * @param $time string
	 * @param $timezone DateTimeZone
	 * @return Date_Time
	 */
	public static function createFromFormat($format, $time, DateTimeZone $timezone = null)
	{
		$dateTime = $timezone
			? parent::createFromFormat($format, $time, $timezone)
			: parent::createFromFormat($format, $time);
		return $timezone
			? new Date_Time($dateTime->format("Y-m-d H:i:s"), $timezone)
			: new Date_Time($dateTime->format("Y-m-d H:i:s"));
	}

	//--------------------------------------------------------------------------------------- fromISO
	/**
	 * @param $date string
	 * @return Date_Time
	 */
	public static function fromISO($date)
	{
		return ($date + 0)
			? new Date_Time($date . substr("2000-01-01 00:00:00", strlen($date)))
			: null;
	}

	//----------------------------------------------------------------------------------------- toISO
	/**
	 * @return string
	 */
	public function toISO()
	{
		return $this->format("Y-m-d H:i:s");
	}

}

date_default_timezone_set();
