<?php
namespace Framework;
use \DateTime;

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
	 * @param  string $format
	 * @param  string $time
	 * @param  DateTimeZone $timezone
	 * @return Date_Time
	 */
	public static function createFromFormat($format, $time, $timezone = null)
	{
		$dateTime = $timezone
			? DateTime::createFromFormat($format, $time, $timezone)
			: DateTime::createFromFormat($format, $time);
		return $timezone
			? new Date_Time($dateTime->format("Y-m-d H:i:s"), $timezone)
			: new Date_Time($dateTime->format("Y-m-d H:i:s"));
	}

	//--------------------------------------------------------------------------------------- fromISO
	/**
	 * @param  string $date
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
