<?php
namespace ITRocks\Framework\Locale;

use DateTime;
use Exception;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Date format locale features : changes date format to comply with user's locale configuration
 */
class Date_Format
{

	//----------------------------------------------------------------------------------- TIME_ALWAYS
	/**
	 * Always display time, always with seconds too
	 */
	const TIME_ALWAYS = 'always';

	//------------------------------------------------------------------------------------- TIME_AUTO
	/**
	 * Default and backward compatibility. Time is added if not 00:00:00, using ::$show_seconds
	 */
	const TIME_AUTO   = 'auto';

	//------------------------------------------------------------------------------------ TIME_NEVER
	/**
	 * Never display time
	 */
	const TIME_NEVER  = 'never';

	//--------------------------------------------------------------------------------------- $format
	/**
	 * @example 'd/m/Y' for the french date format, or 'm/d/Y' for the english one
	 * @var string
	 */
	public $format;

	//--------------------------------------------------------------------------------- $show_seconds
	/**
	 * @var boolean
	 */
	public $show_seconds = false;

	//------------------------------------------------------------------------------------ $show_time
	/**
	 * @var string @values always, auto, never
	 */
	public $show_time = self::TIME_AUTO;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Constructor needs the locale format of the date
	 *
	 * Default date format, if none told, is ISO 'Y-m-d'
	 *
	 * @param $format string eg 'd/m/Y' for the french date format, or 'm/d/Y' for the english one
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
	 * @param $date   string an incomplete locale format date : day alone, year alone, compositions
	 * @param $joker  string if set, the character that replaces missing values, instead of current
	 * @return string the complete locale date eg 2015-30-25
	 */
	private function advancedDate($date, $joker = null)
	{
		// two values with a middle slash
		if (substr_count($date, SL) == 1) {
			list($one, $two) = explode(SL, $date);
			// the first number is a year : year/month
			if (strlen($one) > 2) {
				$date = sprintf('%04s-%02s-' . ($joker ? ($joker . $joker) : '01'), $one, $two);
			}
			// the second number is a year : month/year
			elseif (strlen($two) > 2) {
				$date = sprintf('%04s-%02s-' . ($joker ? ($joker . $joker) : '01'), $two, $one);
			}
			// these are small numbers : day/month or month/day, depending on the locale format
			elseif (strpos($this->format, 'd/m') !== false) {
				$date = sprintf(date('Y') . '-%02s-%02s', $two, $one);
			}
			else {
				$date = sprintf(date('Y') . '-%02s-%02s', $one, $two);
			}
		}
		//echo "date = $date<br>";
		// 1 or 2 digits : day alone : add current month/day and year
		if (in_array(strlen($date), [1, 2])) {
			$date = $joker
				// joker = search : this is a month
				? date('Y-' . sprintf('%02s', $date) . '-' . $joker . $joker)
				// no joker = input : this is the day of the current month
				: date('Y-m-' . sprintf('%02s', $date));
		}
		// 3 and more digits : year alone : add january the 1st
		elseif (is_numeric($date)) {
			$date = sprintf('%04s', $date) . '-01-01';
		}
		//echo "result = $date<br>";
		return $date;
	}

	//------------------------------------------------------------------------------------- appendMax
	/**
	 * Append max date / time to an incomplete ISO date
	 * eg 2015-10-01 will become 2015-10-01 23:59:59
	 *
	 * @param $date string
	 * @return string
	 * @throws Exception
	 */
	public function appendMax($date)
	{
		return Date_Time::fromISO($date, true)->toISO();
	}

	//----------------------------------------------------------------------------------------- toIso
	/**
	 * Takes a locale date and make it ISO
	 *
	 * @param $date  string ie '12/25/2001' '12/25/2001 12:20' '12/25/2001 12:20:16'
	 * @param $max   boolean if true, the incomplete date will be completed to the max range
	 * eg '25/12/2001' will result in '2001-12-25 00:00:00' if false, '2001-12-25 23:59:59' if true
	 * @param $joker string if set, the character that replaces missing values, instead of current
	 * @return string ie '2001-12-25' '2001-12-25 12:20:00' '2001-12-25 12:20:16'
	 */
	public function toIso($date, $max = false, $joker = null)
	{
		if (empty($date)) {
			return '0000-00-00';
		}
		// prevent some user mistyping
		$date = str_replace('//', '/', $date);
		$date = $this->advancedDate($date, $joker);
		if (strlen($date) == 10) {
			if ($max) {
				$date .= SP . '23:59:59';
			}
			elseif ($joker) {
				$date .= SP . $joker . $joker . ':' . $joker . $joker . ':' . $joker . $joker;
			}
			elseif (strpos($date, '-') === false) {
				$datetime = DateTime::createFromFormat($this->format, $date);
				return $datetime ? $datetime->format('Y-m-d') : $date;
			}
			else {
				return $date . SP . (
					$joker
						? ($joker . $joker . ':' . $joker . $joker . ':' . $joker . $joker)
						: '00:00:00'
				);
			}
		}
		elseif (strpos($date, SP)) {
			list($date, $time) = explode(SP, $date);
			$time = explode(':', $time);
			foreach ($time as &$t) {
				if (strlen($t) < 2) {
					$t = '0' . $t;
				}
			}
			while (count($time) < 3) {
				$time[] = $joker ? ($joker . $joker) : ($max ? '59' : '00');
			}
			$time = join(':', $time);
			$datetime = DateTime::createFromFormat($this->format, $date);
			return trim($datetime ? ($datetime->format('Y-m-d') . SP . $time) : $date . SP . $time);
		}
		return $date;
	}

	//-------------------------------------------------------------------------------------- toLocale
	/**
	 * Takes an ISO date and make it locale. Use self::SHOW_TIME to display (or not) time
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
		// new Date_Time will rise exception if input is incorrect
		// DateTime::createFromFormat will rise exception if input is incorrect
		try {
			if (empty($date) || (new Date_Time($date))->isEmpty()) {
				return '';
			}
			if (strlen($date) == 10) {
				return DateTime::createFromFormat('Y-m-d', $date)->format($this->format);
			}
			else {
				list($date, $time) = strpos($date, SP) ? explode(SP, $date) : [$date, ''];
				if ($this->show_time === self::TIME_NEVER) {
					$time = '';
				}
				elseif ($this->show_time !== self::TIME_ALWAYS) {
					if ($time === '00:00:00') {
						$time = '';
					}
					elseif (!$this->show_seconds) {
						$time = substr($time, 0, 5);
					}
				}
				$result = ($date_time = DateTime::createFromFormat('Y-m-d', $date))
					? ($date_time->format($this->format) . (strlen($time) ? (SP . $time) : ''))
					: $date;
				return $result;
			}
		}
		catch (Exception $e) {
			return $this->toLocaleFromDateWithWildcard($date);
		}
	}

	//------------------------------------------------------------------ toLocaleFromDateWithWildcard
	/**
	 * Takes an ISO date possibly having wildcards and make it like locale
	 *
	 * @param $date string|Date_Time ie '2001-12-25' '2001-__-25 12:20:00' '2001-12-25 %%:20:16'
	 * @return string '25/12/2011' '25/??/2001 12:20' '25/12/2001 12:20:16'
	 */
	private function toLocaleFromDateWithWildcard($date)
	{
		static $sub_pattern_date = '([0-9%_]{4}) - ([0-9%_]{2}) - ([0-9%_]{2})';
		static $sub_pattern_time = '([0-9%_]{2}) (?::([0-9%_]{2}))? (?::([0-9%_]{2}))?';
		/** for now it only supports Y,m,d,H,i,s chars in format */
		static $replacement = [
			'Y' => '$1',
			'm' => '$2',
			'd' => '$3',
			'H' => '$4',
			'i' => '$5',
			's' => '$6',
		];
		if (strlen($date) == 10) {
			$pattern = "/ $sub_pattern_date /x";
			if (preg_match($pattern, $date)) {
				$replace = str_replace(array_keys($replacement), array_values($replacement), $this->format);
				return preg_replace($pattern, $replace, $date);
			}
		}
		else {
			$pattern = "/ (?<date>$sub_pattern_date) \\s+ (?<time>$sub_pattern_time) /x";
			if (preg_match($pattern, $date, $matches)) {
				$date = $matches['date'];
				$time = $matches['time'];
				$replace = str_replace(array_keys($replacement), array_values($replacement), $this->format);
				//return str_replace(['_', '%'], ['?', '*'], preg_replace($pattern, $replace, $date));
				$date = preg_replace("/ $sub_pattern_date /x", $replace, $date);
				// backward compatible code but with support to display time if required
				if ($this->show_time == self::TIME_ALWAYS) {
					return trim($date . SP . $time);
				}
				return $date;
				// want code compatible with ::$show_seconds (and new constants) like in toLocale()?
				// => change above backward code with below
				/*if ($this->show_time == self::TIME_NEVER) {
					return $date;
				}
				elseif ($this->show_time != self::TIME_ALWAYS) {
					if ($time === '__:__:__') {
						$time = '';
					}
					elseif (!$this->show_seconds && strlen($time) == 8 && substr($time, -2) == '__') {
						$time = substr($time, 0, 5);
					}
				}
				return trim($date . SP . $time);*/
			}
		}
		// unknown : we return like it is
		return $date;
	}

}
