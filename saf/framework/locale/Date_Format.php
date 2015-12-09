<?php
namespace SAF\Framework\Locale;

use DateTime;
use Exception;
use SAF\Framework\Tools\Date_Time;

/**
 * Date format locale features : changes date format to comply with user's locale configuration
 */
class Date_Format
{

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @var string
	 */
	public $format;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor needs the locale format of the date
	 *
	 * Default date format, if none told, is ISO 'Y-m-d'
	 *
	 * @param $format string ie 'd/m/Y' for french date format
	 */
	public function __construct($format = null)
	{
		if (isset($format)) {
			$this->format = $format;
		}
		if (!isset($this->format)) {
			$this->format = 'Y-m-d';
		}
	}

	//---------------------------------------------------------------------------------- advancedDate
	/**
	 * @param $date string
	 * @return string
	 */
	private function advancedDate($date)
	{
		// 1 or 2 digits : day alone : add current month and year
		if (in_array(strlen($date), [1, 2])) {
			$iso_date = (new DateTime())->format(date('Y-m-' . sprintf('%02s', $date)));
		}
		// 3 and more digits : year alone : add january the 1st
		elseif (is_numeric($date)) {
			$iso_date = (new DateTime())->format(date(sprintf('%04s', $date) . '-01-01'));
		}
		return isset($iso_date) ? $date = (new DateTime($iso_date))->format($this->format) : $date;
	}

	//----------------------------------------------------------------------------------------- toIso
	/**
	 * Takes a locale date and make it ISO
	 *
	 * @param $date string ie '12/25/2001' '12/25/2001 12:20' '12/25/2001 12:20:16'
	 * @param $max  boolean if true, the incomplete date will be completed to the max range
	 * eg '25/12/2001' will result in '2001-12-25 00:00:00' if false, '2001-12-25 23:59:59' if true
	 * @return string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 */
	public function toIso($date, $max = false)
	{
		if (empty($date)) {
			return '0000-00-00';
		}
		$date = $this->advancedDate($date);
		if ($max && (strlen($date) == 10)) {
			$date .= SP . '23:59:59';
		}
		if (strlen($date) == 10) {
			$datetime = DateTime::createFromFormat($this->format, $date);
			return $datetime ? $datetime->format('Y-m-d') : $date;
		}
		elseif (strpos($date, SP)) {
			list($date, $time) = explode(SP, $date);
			while (strlen($time) < 8) {
				$time .= $max ? ':59' : ':00';
			}
			$datetime = DateTime::createFromFormat($this->format, $date);
			return trim($datetime
				? ($datetime->format('Y-m-d') . SP . $time)
				: $date . SP . $time
			);
		}
		else {
			return $date;
		}
	}

	//-------------------------------------------------------------------------------------- toLocale
	/**
	 * Takes an ISO date and make it locale
	 *
	 * @param $date string|Date_Time ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 * @return string '25/12/2011' '25/12/2001 12:20' '25/12/2001 12:20:16'
	 */
	public function toLocale($date)
	{
		// in case of $date being an object, ie Date_Time, get an ISO date only
		if ($date instanceof DateTime) {
			$date = $date->format('Y-m-d H:i:s');
		}
		try {
			if (empty($date) || (new Date_Time($date))->isMin()) {
				return '';
			}
		}
		catch (Exception $e) {
			return $date;
		}
		if (strlen($date) == 10) {
			return DateTime::createFromFormat('Y-m-d', $date)->format($this->format);
		}
		else {
			list($date, $time) = strpos($date, SP) ? explode(SP, $date) : [$date, ''];
			if ((strlen($time) == 8) && (substr($time, -3) == ':00')) {
				substr($time, 0, 5);
			}
			$result = DateTime::createFromFormat('Y-m-d', $date)->format($this->format) . SP . $time;
			if (substr($result, -9) == ' 00:00:00') {
				$result = substr($result, 0, -9);
			}
			elseif (substr($result, -3) == ':00') {
				$result = substr($result, 0, -3);
			}
			return $result;
		}
	}

}
