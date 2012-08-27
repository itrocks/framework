<?php

class Date_Utils
{

	//--------------------------------------------------------------------------------------- fromISO
	/**
	 * @param  string $date
	 * @return DateTime
	 */
	public static function fromISO($date)
	{
		$length = strlen($date);
		switch ($length) {
			case 19 : return DateTime::createFromFormat("Y-m-d H:i:s", $date);
			case 16 : return DateTime::createFromFormat("Y-m-d H:i", $date);
			case 13 : return DateTime::createFromFormat("Y-m-d H", $date);
			case 10 : return DateTime::createFormFormat("Y-m-d", $date);
			case  7 : return DateTime::createFormFormat("Y-m-d", "$date-01");
			case  4 : return DateTime::createFormFormat("Y-m-d", "$date-01-01");
		}
		return null;
	}

	//----------------------------------------------------------------------------------------- toISO
	/**
	 * @param  DateTime $date
	 * @return string
	 */
	public static function toISO($date)
	{
		return $date->format("Y-m-d H:i:s");
	}

}
