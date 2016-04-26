<?php
namespace SAF\Framework\Widget\Data_List;

use SAF\Framework\Dao\Func;
use SAF\Framework\Dao\Func\Range;
//use SAF\Framework\Dao\Func\Comparison;
use SAF\Framework\Dao\Option;
//use SAF\Framework\Locale\Date_Format;
use SAF\Framework\Locale\Loc;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Reflection\Reflection_Property;
use SAF\Framework\Reflection\Type;
use SAF\Framework\Tools\Date_Time;

/**************************************************/

/**
 * SEARCH GRAMMAR:
 * spaces are optionals
 * search       = orexpr
 * orexpr       = andexpr [, andexpr [...]]]
 * andexpr      = notexpr [& notexpr [...]]
 * notexpr      = ["!"]complexvalue
 * complexvalue = singlevalue
 * singlevalue  = scalar | emptyword
 * scalar       = sentence that may contains jokers
 * joker        = "?" | "*" | "%" | "_"
 *
 * >>especially for Date_Time, Float, Integer, String fields
 * complexvalue = range | singlevalue
 * range        = minrgnvalue "-" maxrngvalue
 *
 * >>especially for Float, Integer, String fields
 * minrngvalue  = scalar
 * maxrngvalue  = scalar
 *
 * >>especially for Date_Time
 * singlevalue  = dateperiod   (that will be updated to both a min and a max value of the period)
 * minrngvalue  = daterngvalue (that will be updated to its min value)
 * maxrngvalue  = daterngvalue (that will be updated to its max value)
 * daterngvalue = dateperiod without any joker
 * dateperiod   = dateword | emptyword | wildcard
 *              | [d]d/[m]m/yyyy
 *              | [m]m/yyyy | yyyy/[m]m (means from 01/mm/yyyy to 31!/mm/yyyy) 3-4 chars mandatory for yyyy
 *              | [d]d/[m]m         (means implicit current year)
 *              | yyyy              (means from 01/01/yyyy to 31/12/yyyy) 3-4 chars mandatory
 *              | [d]d              (means implicit current month and year)
 *              | "y" [+|-] integer (means from 01/01/yyyy to 31/12/yyyy)
 *              | "m" [+|-] integer (means from 01/mm/currentyear to 31!/mm/currentyear)
 *              | "d" [+|-] integer (means implicit current month and year)
 * dateword     = "currentyear" | "currentmonth" | déclinaisons françaises
 *              | "today" | "currentday" | déclinaisons françaises
 *              | "now" (means with current time?)
 * emptyword    = "vide" | "nul" | "aucun"
 * dd           = #[0-3?]?[0-9?]|*#  |  "d" (+|-) integer
 * mm           = #[0-1?]?[0-9?]|*# | "m" (+|-) integer
 * yyyy         = #[0-9?]{4}|*# | "y" (+|-) integer //is it possible to check year about "*" only? we can not be sure this is a year!
 *
 * If there is any joker (*?) on a dd, mmm or yyyy, it will be converted to a LIKE search
 * Otherwise any date will be converted to a period from midnight to 23h59:59
 * For ranges, min date will be converted to midnight and maxdate to 23h59:59
 *
 */


/**
 * Search parameters parser
 *
 * Parse user-input search strings to get valid search arrays in return
 */
class Search_Parameters_Parser
{
	const NOT_A_RANGE_VALUE = 0;
	const MIN_RANGE_VALUE = -1;
	const MAX_RANGE_VALUE = 1;

	//---------------------------------------------------------------------------------------- $class
	/**
	 * @var Reflection_Class
	 */
	public $class;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var array
	 */
	public $search;

	//------------------------------------------------------------------------------ $currentDateTime
	/**
	 * @var Date_Time
	 */
	protected $currentDateTime;

	//---------------------------------------------------------------------------------- $currentYear
	/**
	 * @var string (numeric)
	 */
	protected $currentYear;

	//--------------------------------------------------------------------------------- $currentMonth
	/**
	 * @var string (numeric)
	 */
	protected $currentMonth;

	//----------------------------------------------------------------------------------- $currentDay
	/**
	 * @var string (numeric)
	 */
	protected $currentDay;

	//---------------------------------------------------------------------------------- $currentHour
	/**
	 * @var string (numeric)
	 */
	protected $currentHour;

	//------------------------------------------------------------------------------- $currentMinutes
	/**
	 * @var string (numeric)
	 */
	protected $currentMinutes;

	//------------------------------------------------------------------------------- $currentSeconds
	/**
	 * @var string (numeric)
	 */
	protected $currentSeconds;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, $search)
	{
		$this->class  = new Reflection_Class($class_name);
		$this->search = $search;
		$this->currentDateTime = Date_Time::now();
		$this->currentYear = $this->currentDateTime->format('Y');
		$this->currentMonth = $this->currentDateTime->format('m');
		$this->currentDay = $this->currentDateTime->format('d');
		$this->currentHour = $this->currentDateTime->format('H');
		$this->currentMinutes = $this->currentDateTime->format('i');
		$this->currentSeconds = $this->currentDateTime->format('s');
	}

	//----------------------------------------------------------------------------- getDateSubPattern
	/**
	 * get the PCRE Pattern of a date that may contain formula in its part
	 * e.g 1/m-1 | 1/m+2/y-1 | d-7 | ...
	 * Note: this is not the complete pattern, you should surround by delimiters
	 * and add whatever else you want
	 *
	 * @return string
	 */
	protected static function getDateSubPattern()
	{
		static $pattern = false;
		if (!$pattern) {
			$letters = self::getLetters(Date_Time::YEAR)
				. self::getLetters(Date_Time::MONTH)
				. self::getLetters(Date_Time::DAY);
			$pattern = '(?:(?:[0-9*?%_]{1,4} | ['.$letters.'](?:[-+]\d+)?) [\/]){0,2} (?:[0-9*?%_]{1,4} | ['.$letters.'](?:[-+]\d+)?)';
		}
		return $pattern;
	}

	//------------------------------------------------------------------------------------ getLetters
	/**
	 * get the letters that can be used in formula for a part of a date
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR
	 * @return string
	 */
	protected static function getLetters($part)
	{
		static $letters = false;
		if (!$letters) {
			$letters = Loc::tr('d|m|y');
			$letters = explode('|', $letters);
			$letters = [
				Date_Time::DAY => 'dD'.$letters[0].strtoupper($letters[0]),
				Date_Time::MONTH => 'mM'.$letters[1].strtoupper($letters[1]),
				Date_Time::YEAR => 'yY'.$letters[2].strtoupper($letters[2])
			];
		}
		return $letters[$part];
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @return array search-compatible search array
	 */
	public function parse()
	{
		$search = $this->search;
		foreach ($search as $property_path => &$search_value) {
			$property = new Reflection_Property($this->class->name, $property_path);
			if (strlen(trim($search_value))) {
				$this->parseField($search_value, $property);
			}
		}
		return $search;
	}

	//------------------------------------------------------------------------------------ parseField
	/**
	 * @param $search_value string
	 * @param $property Reflection_Property
	 */
	public function parseField(&$search_value, Reflection_Property $property)
	{
		try {
			$search_value = $this->applyOr($search_value, $property);
		}
		catch (\Exception $e) {
			$search_value = $e;
		}
	}

	//--------------------------------------------------------------------------------------- applyOr
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Func
	 */
	protected function applyOr($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (strpos($search_value, ',') !== false)) {
			$or = [];
			foreach (explode(',', $search_value) as $search) {
				$or[] = $this->applyAnd($search, $property);
			}
			return Func::orOp($or);
		} else {
			$search = $this->applyAnd($search_value, $property);
			return $search;
		}
	}

	//-------------------------------------------------------------------------------------- applyAnd
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Func
	 */
	protected function applyAnd($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (strpos($search_value, '&') !== false)) {
			$and = [];
			foreach (explode('&', $search_value) as $search) {
				$and[] = $this->applyNot($search, $property);
			}
			return Func::andOp($and);
		}
		else {
			$search = $this->applyNot($search_value, $property);
			return $search;
		}
	}

	//-------------------------------------------------------------------------------------- applyNot
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Func
	 */
	protected function applyNot($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (substr($search_value, 0, 1) === '!')) {
			$search_value = substr($search_value, 1);
			$search = $this->applyComplexValue($search_value, $property);
			if ($search instanceof Func\Negate) {
				$search->negate();
			} else {
				$search = Func::notEqual($search);
			}
		} else {
			$search = $this->applyComplexValue($search_value, $property);
		}
		return $search;
	}

	//----------------------------------------------------------------------------- applyComplexValue
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return mixed        Range | Func | scalar
	 * @throws Data_List_Exception
	 */
	protected function applyComplexValue($search_value, Reflection_Property $property)
	{
		if ($this->isRange($search_value, $property)) {
			if ($this->hasRange($property)) {
				$search = $this->applyRange($search_value, $property);
			}
			else {
				throw new Data_List_Exception($search_value, "Range not permitted");
			}
		}
		else {
			$search = $this->applySingleValue($search_value, $property);
		}
		return $search;
	}

	//------------------------------------------------------------------------------ applySingleValue
	/**
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @return mixed
	 * @throws Data_List_Exception
	 */
	protected function applySingleValue($search_value, Reflection_Property $property)
	{
		$type = $property->getType()->asString();
		switch ($type) {
			// Date_Time type
			case Date_Time::class: {
				$search = $this->applyDatePeriod($search_value);
				break;
			}
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default: {
				if (($search = $this->applyEmptyWord($search_value, $type)) !== false) {
					break;
				}
				$search = $this->applyScalar($search_value, $property);
				break;
			}
		}
		if ($search === false) {
			throw new Data_List_Exception($search_value, "Error in expression");
		}
		return $search;
	}

	//--------------------------------------------------------------------------------------- isRange
	/**
	 * Check if expression is a range expression
	 * @param $search_value string
	 * @param $property Reflection_Property
	 * @return boolean
	 */
	public function isRange($search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Float | Integer | String types
			case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
				if (is_string($search_value) && (strpos($search_value, '-') !== false)) {
					return true;
				}
				break;
			}
			// Date_Time type
			case Date_Time::class: {
				if (
					is_string($search_value) &&
					!$this->isASingleDateFormula($search_value) &&  // take care of formula that may contains char '-'
					(strpos($search_value, '-') !== false)
				) {
					return true;
				}
				break;
			}
		}
		return false;
	}

	//-------------------------------------------------------------------------- isASingleDateFormula
	/**
	 * check if expression if a single date containing formula
	 * @param $expr  string
	 * @return bool
	 */
	protected function isASingleDateFormula($expr)
	{
		//we check if $expr is a single date containing formula
		//but it may be a range with 2 dates containing formula, what should return false
		//so the use of /^ ... $/
		$pattern = self::getDateSubPattern();
		if (preg_match("/^ \\s* $pattern \\s* $/x", $expr)) {
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------ applyRange
	/**
	 * Apply a range expression on search string. The range is supposed to exist!
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @return Range
	 * @throws Data_List_Exception
	 */
	protected function applyRange($search_value, Reflection_Property $property)
	{
		$range = $this->getRangeParts($search_value, $property);
		$range[0] = $this->applyRangeValue($range[0], $property, self::MIN_RANGE_VALUE);
		$range[1] = $this->applyRangeValue($range[1], $property, self::MAX_RANGE_VALUE);
		if ($range[0] === false || $range[1] === false) {
			throw new Data_List_Exception($search_value, "Error in range expression or range must have 2 parts only");
		}

		$search = new Range($range[0], $range[1]);
		return $search;
	}

	//--------------------------------------------------------------------------------- getRangeParts
	/**
	 * Apply a range expression on search string. The range is supposed to exist!
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @return array
	 * @throws Data_List_Exception
	 */
	protected function getRangeParts($search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class: {
				//Take care of char of formulas on expr like 'm-3-m', '01/m-2/2015-01/m-2/2016'...
				//pattern of a date that may contain formula
				$pattern = self::getDateSubPattern();
				//we should analyse 1st the right pattern to solve cases like 1/5/y-1/7/y
				//we should parse like min=1/5/y   and max=1/7/y
				//  and not parse like min=1/5/y-1 and max=/7/y
				$patternright = "/[-](\\s* $pattern \\s* )$/x";
				$found = preg_match($patternright, $search_value, $matches);
				if ($found) {
					$max = trim($matches[1]);
					$min = trim(substr($search_value, 0, -(strlen($matches[1]) + 1)));
					$range = [$min, $max];
				} else {
					throw new Data_List_Exception($search_value, "Error in range expression or range must have 2 parts only");
				}
				break;
			}
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default: {
				$range = explode('-', $search_value, 2);
				//check we have only two parts in the range!
				if (implode('-', $range) !== $search_value) {
					throw new Data_List_Exception($search_value, "Range must have 2 parts only");
				}
				break;
			}
		}
		return $range;
	}

	//------------------------------------------------------------------------------- applyRangeValue
	/**
	 * @param $search_value string|Option
	 * @param $property     Reflection_Property
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed
	 */
	protected function applyRangeValue($search_value, Reflection_Property $property, $minmax)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// Date_Time type
			case Date_Time::class: {
				$search = $this->applyDateRangeValue($search_value, $minmax);
				break;
			}
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default: {
				$search = $this->applyScalar($search_value, $property, true);
				break;
			}
		}
		return $search;
	}

	//----------------------------------------------------------------------------------- applyScalar
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @param $isRangeValue boolean  true if we parse a range value
	 * @return string
	 */
	protected function applyScalar(
		$search_value,
		/** @noinspection PhpUnusedParameterInspection */ Reflection_Property $property,
		$isRangeValue = false
	)
	{
		$search = $this->applyJokers($search_value, $isRangeValue);
		return $search;
	}

	//--------------------------------------------------------------------------- applyDateRangeValue
	/**
	 * @param $search_value string|Option
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed
	 * @throws Data_List_Exception
	 */
	protected function applyDateRangeValue($search_value, $minmax)
	{
		if ($this->hasJoker($search_value)) {
			throw new Data_List_Exception($search_value, "Range value can not have wildcard");
		}
		$search = $this->applyDatePeriod($search_value, $minmax);
		return $search;
	}

	//------------------------------------------------------------------------------- applyDatePeriod
	/**
	 * @param $search_value string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed
	 */
	protected function applyDatePeriod($search_value, $minmax = self::NOT_A_RANGE_VALUE)
	{
		if (($date = $this->applyDateSingleJoker($search_value)) !== false) {
			return $date;
		}
		if (($date = $this->applyDateWord($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applyDateEmptyWord($search_value)) !== false) {
			return $date;
		}
		if (($date = $this->applyDayMonthYear($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applyMonthYear($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applyDayMonth($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applyYearOnly($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applyDayOnly($search_value, $minmax)) !== false) {
			return $date;
		}
		if (($date = $this->applySingleFormula($search_value, $minmax, Date_Time::YEAR)) !== false) {
			return $date;
		}
		if (($date = $this->applySingleFormula($search_value, $minmax, Date_Time::MONTH)) !== false) {
			return $date;
		}
		if (($date = $this->applySingleFormula($search_value, $minmax, Date_Time::DAY)) !== false) {
			return $date;
		}
		return false;
	}

	//-------------------------------------------------------------------------- applyDateSingleJoker
	/**
	 * If expression is a single wildcard or series of wildcard chars, convert to corresponding date
	 * @param $expr         string
	 * @return boolean|mixed false
	 */
	protected function applyDateSingleJoker($expr)
	{
		if (is_string($expr) && preg_match('/^ [*%?_]+ $/x', $expr)) {
			return Func::like("____-__-__ __:__:__");
		}
		return false;
	}

	//--------------------------------------------------------------------------------- applyDateWord
	/**
	 * If expression is a date word, convert to corresponding date
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed|boolean false
	 */
	protected function applyDateWord($expr, $minmax)
	{
		//TODO: RETURN THE PERIOD!
		/**
		 * TODO: iconv with //TRANSLIT requires that locale is different than C or Posix. To Do: a better support!!
		 * See: http://php.net/manual/en/function.iconv.php#74101
		 */

		$word = preg_replace('/\s|\'/','',strtolower(iconv('UTF-8', 'ASCII//TRANSLIT', $expr)));
//TODO: change by using Loc::rtr($word) ??
		if (in_array($word, ['currentyear', 'anneecourante', 'anneeencours'])) {
			//we convert a current year word in numeric current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0,0,0,1,1,$this->currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(23,59,59,12,31,$this->currentYear)
			);
		} elseif (in_array($word, ['currentmonth', 'moiscourant', 'moisencours'])) {
			//we convert a current year word in numeric current month / current year period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0,0,0,$this->currentMonth,1,$this->currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s', mktime(0,0,-1,$this->currentMonth+1,1,$this->currentYear)
			);
		} elseif (in_array($word, ['today', 'currentday', 'jourcourant', 'jourencours', 'aujourd\'hui', 'aujourdhui'])) {
			//we convert a current day word in numeric current day period
			$date_begin = date(
				'Y-m-d H:i:s', mktime(0, 0, 0, $this->currentMonth, $this->currentDay, $this->currentYear)
			);
			$date_end = date(
				'Y-m-d H:i:s',
				mktime(23, 59, 59, $this->currentMonth, $this->currentDay, $this->currentYear)
			);
		}
		if (isset($date_begin) && isset($date_end)) {
			$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
			return $date;
		}
		return false;
	}

	//-------------------------------------------------------------------------------- applyEmptyWord
	/**
	 * If expression is a date empty word, convert to corresponding value
	 * @param $expr         string
	 * @param $type         string
	 * @return mixed|boolean false
	 */
	protected function applyEmptyWord($expr, $type)
	{
		if ($this->isEmptyWord($expr)) {
			$value = in_array($type, [Type::BOOLEAN, Type::FLOAT, Type::INTEGER]) ? 0 : '';
			$date = Func::orOp([$value, Func::isNull()]);
			return $date;
		}
		//not an empty word
		return false;
	}

	//---------------------------------------------------------------------------- applyDateEmptyWord
	/**
	 * If expression is a date empty word, convert to corresponding value
	 * @param $expr         string
	 * @return mixed|boolean false
	 */
	protected function applyDateEmptyWord($expr)
	{
		if ($this->isEmptyWord($expr)) {
			$value = '0000-00-00 00:00:00';
			$date = Func::orOp([$value, Func::isNull()]);
			return $date;
		}
		//not an empty word
		return false;
	}

	//--------------------------------------------------------------------------------- applyYearOnly
	/**
	 * apply if expression is a year
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed|bool false
	 * @throws Data_List_Exception
	 */
	protected function applyYearOnly($expr, $minmax)
	{
		//no slash and (>3 digit or "y" or "a")
		//if (!substr_count($expr, SL) && (strlen($expr)>2 || preg_match('/y|a/', $expr))) {
		if (preg_match('/^ \s* ([0-9*?%_]{3,4} | ([yaYA]([-+]\d+)?)) \s* $/x', $expr)) {
			$year = $expr;
			if ($this->computeYear($year)) {
				if ($this->hasJoker($year)) {
					list($day, $month, $year) = $this->padDateParts('__', '__', $year);
					$date = Func::like("$year-$month-$day __:__:__");
				}
				else {
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $year));
					$date_end = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $year));
					$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
					return $date;
				}
				return $date;
			}
			//bad expression?
			//TODO: Remove Exception or make a support for error?
			throw new Data_List_Exception($expr, "Error in year expression");
		}
		return false;
	}

	//-------------------------------------------------------------------------------- applyMonthYear
	/**
	 * apply if expression is a month/year
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed|bool false
	 * @throws Data_List_Exception
	 */
	protected function applyMonthYear($expr, $minmax)
	{
		// two values with a middle slash
		if (substr_count($expr, SL) == 1) {
			list($one, $two) = explode(SL, $expr);
			if (
				(strlen($one) > 2 && !preg_match('/^ \s* [mM]([-+]\d+)? $/x', $one)) ||
				preg_match('/^ \s* [yaYA]([-+]\d+)? $/x', $one)
			) {
				// the first number is a year or contains 'y' or 'a' : year/month
				$year = $one;
				$month = $two;
			} elseif (
				(strlen($two) > 2 && !preg_match('/^ \s* [mM]([-+]\d+)? $/x', $two)) ||
				preg_match('/^ [yaYA]([-+]\d+)? \s* $/x', $two)) {
				// the second number is a year or contains 'y' or 'a' : month/year
				$year = $two;
				$month = $one;
			} else {
				//else, may be day/month or month/day => supported elsewhere
				return false;
			}
			if (!$this->computeMonth($month)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in month expression");
			}
			if (!$this->computeYear($year)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in year expression");
			}
			$date = $this->_buildMonthYear($month, $year, $minmax, $expr);
			return $date;
		}
		return false;
	}

	//------------------------------------------------------------------------------- _buildMonthYear
	/**
	 * build the date from computed month and a year
	 * @param $month      string
	 * @param $year       string
	 * @param $minmax     integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @param $expr       string
	 * @return Func\Comparison|Range
	 * @throws Data_List_Exception
	 */
	private function _buildMonthYear($month, $year, $minmax, $expr)
	{
		$monthHasJoker = $this->hasJoker($month);
		$yearHasJoker = $this->hasJoker($year);
		if (!$monthHasJoker && !$yearHasJoker) {
			$date_begin = date('Y-m-d H:i:s', mktime(0,0,0,$month,1,$year));
			$date_end = date('Y-m-d H:i:s', mktime(0,0,-1,((int)$month+1),1,$year));
			$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
		} elseif (!$yearHasJoker) {
			//month has wildcard, year may be computed
			list($day, $month, $year) = $this->padDateParts('__', $month, $year);
			$date = Func::like("$year-$month-$day __:__:__");
		} elseif (!$monthHasJoker) {
			//year has wildcard but not month that may be computed.
			//So we should take care if month is <1 or >12
			if ($month < 1 || $month > 12) {
				throw new Data_List_Exception($expr, "You can not put a formula on month when year has wildcard!");
			}
			list($day, $month, $year) = $this->padDateParts('__', $month, $year);
			$date = Func::like("$year-$month-$day __:__:__");
		} else {
			//both year and month have wildcards
			list($day, $month, $year) = $this->padDateParts('__', $month, $year);
			$date = Func::like("$year-$month-$day __:__:__");
		}
		return $date;
	}

	//--------------------------------------------------------------------------------- applyDayMonth
	/**
	 * apply if expression is a day/month or month/day
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed|bool false
	 * @throws Data_List_Exception
	 */
	protected function applyDayMonth($expr, $minmax)
	{
		// two values with a middle slash
		if (substr_count($expr, SL) == 1) {
			list($one, $two) = explode(SL, $expr);
			// these should be small numbers : day/month or month/day, depending on the locale format
			if (strpos(Loc::date()->format, 'd/m') !== false) {
				// day/month
				$month = $two;
				$day = $one;
			} else {
				// month/day
				$month = $one;
				$day = $two;
			}
			if (!$this->computeDay($day)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in day expression");
			}
			if (!$this->computeMonth($month)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in month expression");
			}
			$date = $this->_buildDayMonth($day, $month, $minmax, $expr);
			return $date;
		}
		return false;
	}

	//-------------------------------------------------------------------------------- _buildDayMonth
	/**
	 * build the date from computed month and a year
	 * @param $day        string
	 * @param $month      string
	 * @param $minmax     integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @param $expr       string
	 * @return Func\Comparison|Range
	 * @throws Data_List_Exception
	 */
	private function _buildDayMonth($day, $month, $minmax, $expr)
	{
		$dayHasJoker = $this->hasJoker($day);
		$monthHasJoker = $this->hasJoker($month);
		if (!$dayHasJoker && !$monthHasJoker) {
			//none has wildcard
			$date_begin = date('Y-m-d H:i:s', mktime(0,0,0,$month,$day,$this->currentYear));
			$date_end = date('Y-m-d H:i:s', mktime(0,0,-1,$month,(int)$day+1,$this->currentYear));
			$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
		} else {
			//at least one has wildcard
			if ($minmax != self::NOT_A_RANGE_VALUE) {
				//we can not have wildcard on a range value
				throw new Data_List_Exception($expr, "You can not have a wildcard on a range value!");
			}
			if (!$monthHasJoker) {
				//day has wildcard, month may be computed
				//try to correct month and year
				$time = mktime(0, 0, 0, $month, 1, $this->currentYear);
				$year = date('Y', $time);
				$month = date('m', $time);
				list($day, $month, $year) = $this->padDateParts($day, $month, $year);
				$date = Func::like("$year-$month-$day __:__:__");
			}
			elseif (!$dayHasJoker) {
				//month has wildcard but not day that may be computed.
				//So we should take care if day is <1 or >31 //TODO:what about 30? 29? 28?
				if ($day < 1 || $day > 31) {
					throw new Data_List_Exception($expr, "You can not put a formula on day when month has wildcard!");
				}
				list($day, $month) = $this->padDateParts($day, $month, 'fooo');
				$date = Func::like("{$this->currentYear}-$month-$day __:__:__");
			}
			else {
				//both day and month have wildcards
				list($day, $month) = $this->padDateParts($day, $month, 'fooo');
				$date = Func::like("{$this->currentYear}-$month-$day __:__:__");
			}
		}
		return $date;
	}

	//----------------------------------------------------------------------------- applyDayMonthYear
	/**
	 * apply if expression is a day/month/year or month/day/year
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return mixed|bool false
	 * @throws Data_List_Exception
	 */
	protected function applyDayMonthYear($expr, $minmax)
	{
		// three values with a middle slash
		if (substr_count($expr, SL) == 2) {
			list($one, $two, $three) = explode(SL, $expr);
			if (Loc::date()->format == 'd/m/Y') {
				// day/month/year
				$day = $one;
				$month = $two;
				$year = $three;
			} else {
				// month/day/year
				$day = $two;
				$month = $one;
				$year = $three;
			}
			if (!$this->computeDay($day)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in day expression");
			}
			if (!$this->computeMonth($month)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in month expression");
			}
			if (!$this->computeYear($year)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in year expression");
			}
			$date = $this->_buildDayMonthYear($day, $month, $year, $minmax, $expr);
			return $date;
		}
		return false;
	}

	//---------------------------------------------------------------------------- _buildDayMonthYear
	/**
	 * build the date from computed day, month and year
	 * @param $day        string
	 * @param $month      string
	 * @param $year       string
	 * @param $minmax     integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @param $expr       string
	 * @return Func\Comparison|Range
	 * @throws Data_List_Exception
	 */
	private function _buildDayMonthYear($day, $month, $year, $minmax, $expr)
	{
		$dayHasJoker = $this->hasJoker($day);
		$monthHasJoker = $this->hasJoker($month);
		$yearHasJoker = $this->hasJoker($year);
		if (!$dayHasJoker && !$monthHasJoker && !$yearHasJoker) {
			//none has wildcard
			$date_begin = date('Y-m-d H:i:s', mktime(0,0,0,$month,$day,$year));
			$date_end = date('Y-m-d H:i:s', mktime(0,0,-1,$month,(int)$day+1,$year));
			$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
		} else {
			//at least one has wildcard
			if ($minmax != self::NOT_A_RANGE_VALUE) {
				//we can not have wildcard on a range value
				throw new Data_List_Exception($expr, "You can not have a wildcard on a range value!");
			}
			if (
				//000: all have wildcards
				($dayHasJoker && $monthHasJoker && $yearHasJoker) ||
				//001: day has wildcard, month has wildcard, year may be computed
				($dayHasJoker && $monthHasJoker && !$yearHasJoker)
			) {
				///no need to correct anything!
			}
			if (
				//010: day has wildcard, month may be computed, year has wildcard
				($dayHasJoker && !$monthHasJoker && $yearHasJoker)
			) {
				if ($month < 1 || $month > 12) {
					throw new Data_List_Exception($expr, "You can not put a formula on month when year has wildcard!");
				}
			}
			if (
				//011: day has wildcard, month may be computed, year may be computed
				($dayHasJoker && !$monthHasJoker && !$yearHasJoker)
			) {
				//try to correct month and year
				$time = mktime(0, 0, 0, $month, 1, $year);
				$year = date('Y', $time);
				$month = date('m', $time);
			}
			if (
				//100: day may be computed, month has wildcard, year has wildcard
				(!$dayHasJoker && $monthHasJoker && $yearHasJoker) ||
				//101: day may be computed, month has wildcard, year may be computed
				(!$dayHasJoker && $monthHasJoker && !$yearHasJoker)
			) {
				//So we should take care if day is <1 or >31 //TODO:what about 30? 29? 28?
				if ($day < 1 || $day > 31) {
					throw new Data_List_Exception($expr, "You can not put a formula on day when month has wildcard!");
				}
			}
			list($day, $month, $year) = $this->padDateParts($day, $month, $year);
			$date = Func::like("$year-$month-$day __:__:__");
		}
		return $date;
	}

	//---------------------------------------------------------------------------------- applyDayOnly
	/**
	 * apply if expression is a day only
	 * @param $expr         string
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @return bool|mixed false
	 * @throws Data_List_Exception
	 */
	protected function applyDayOnly($expr, $minmax)
	{
		//two chars or a single joker or formula
		if (preg_match('/^ \s* ([*%?_] | [0-9*?%_]{1,2} | ([djDJ]([-+]\d+)?)) \s* $/x', $expr)) {
			$day = $expr;
			if (!$this->computeDay($day)) {
				//bad expression?
				//TODO: Remove Exception or make a support for error?
				throw new Data_List_Exception($expr, "Error in day expression");
			}
			if ($this->hasJoker($day)) {
				//$date_begin = "{$this->currentYear}-{$this->currentMonth}-{$day} 00:00:00";
				//$date_end = "{$this->currentYear}-{$this->currentMonth}-{$day} 23:59:59";
				list($day, $month, $year) = $this->padDateParts($day, $this->currentMonth, $this->currentYear);
				$date = Func::like("{$year}-{$month}-{$day} __:__:__");
			}
			else {
				$date_begin = date(
					'Y-m-d H:i:s', mktime(0, 0, 0, $this->currentMonth, $day, $this->currentYear)
				);
				$date_end = date(
					'Y-m-d H:i:s', mktime(23, 59, 59, $this->currentMonth, $day, $this->currentYear)
				);
				$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
			}
			return $date;
		}
		return false;
	}

	//---------------------------------------------------------------------------- applySingleFormula
	/**
	 * Apply a formula that is alone in the expression (eg. not "15/m+1/2016" but only "m+1")
	 *
	 * @param &$expr string formula
	 * @param $minmax       integer  ::MIN_RANGE_VALUE | ::MAX_RANGE_VALUE | ::NOT_A_RANGE_VALUE
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return string|Range
	 */
	protected function applySingleFormula($expr, $minmax, $part)
	{
		if ($this->_computeFormula($expr, $part)) {
			switch ($part) {
				case Date_Time::YEAR:
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, 1, 1, $expr));
					$date_end = date('Y-m-d H:i:s', mktime(23, 59, 59, 12, 31, $expr));
					break;
				case Date_Time::MONTH:
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, $expr, 1, $this->currentYear));
					$date_end = date('Y-m-d H:i:s', mktime(0, 0, -1, (int)$expr+1, 1, $this->currentYear));
					break;
				case Date_Time::DAY:
				default: //here to avoid inspection
					$date_begin = date('Y-m-d H:i:s', mktime(0, 0, 0, $this->currentMonth, $expr, $this->currentYear));
					$date_end = date('Y-m-d H:i:s', mktime(23, 59, 59, $this->currentMonth, $expr, $this->currentYear));
					break;
			}
			$date = $this->buildDateOrPeriod($date_begin, $date_end, $minmax);
			return $date;
		}
		return false;
	}

	//----------------------------------------------------------------------------------- applyJokers
	/**
	 * @param $search_value string
	 * @param $isRangeValue boolean  true if we parse a range value
	 * @return string
	 */
	protected function applyJokers($search_value, $isRangeValue = false)
	{
		if (is_string($search_value)) {
			//$search = str_replace(['*', '?'], ['%', '_'], $search_value);
			$search = preg_replace(['/[*%]/', '/[?_]/'], ['%', '_'], $search_value, -1, $count);
			if ($count && !$isRangeValue) {
				$search = Func::like($search);
			} /*else {
				$search = Func::equal($search);
			}*/
			return $search;
		}
		return $search_value;
	}

	//-------------------------------------------------------------------------------------- hasJoker
	/** Check if expression has any wildcard
	 * @param $search_value string
	 * @return boolean
	 */
	protected function hasJoker($search_value)
	{
		if (preg_match('/[*?%_]/', $search_value)) {
			return true;
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- hasRange
	/**
	 * Check if a property has right to have range in search string
	 *
	 * @param $property Reflection_Property
	 * @return boolean true if range supported and authorized
	 */
	public function hasRange(Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		return ($property->getAnnotation('search_range')->value !== false)
			&& in_array($type_string, [Date_Time::class, Type::FLOAT, Type::INTEGER, Type::STRING]);
	}

	//----------------------------------------------------------------------------------- computeYear
	/**
	 * Compute a year expression to get a string suitable to build a Date
	 *
	 * @param $expr string numeric or with wildcard or formula like y+1 | y+3 | a+1 | a+3... returns computed if any
	 * @return boolean
	 */
	public function computeYear(&$expr)
	{
		$expr = trim($expr);
		//numeric expr
		if ($this->_checkNumericExpr($expr)) {
			return true;
		}
		//expr with wildcards
		if ($this->_checkDateWildcardExpr($expr, Date_Time::YEAR)) {
			return true;
		}
		//expr with formula
		if ($this->_computeFormula($expr, Date_Time::YEAR)) {
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------------------- computeMonth
	/**
	 * Compute a month expression to get a string suitable to build a Date
	 *
	 * @param $expr string numeric or with widlcard or formula m+1 | m+3 | m-2... returns computed if any
	 * @return boolean
	 */
	public function computeMonth(&$expr)
	{
		$expr = trim($expr);
		//numeric expr
		if ($this->_checkNumericExpr($expr)) {
			return true;
		}
		//expr with wildcards
		if ($this->_checkDateWildcardExpr($expr, Date_Time::MONTH)) {
			return true;
		}
		//expr with formula
		if ($this->_computeFormula($expr, Date_Time::MONTH)) {
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------ computeDay
	/**
	 * Compute a day expression to get a string suitable to build a Date
	 *
	 * @param $expr string numeric or with widlcard or formula d+1 | d+3 | d-2 | j+1 | j+3 | j-2... returns computed if any
	 * @return boolean
	 */
	public function computeDay(&$expr)
	{
		$expr = trim($expr);
		//numeric expr
		if ($this->_checkNumericExpr($expr)) {
			return true;
		}
		//expr with wildcards
		if ($this->_checkDateWildcardExpr($expr, Date_Time::DAY)) {
			return true;
		}
		//expr with formula
		if ($this->_computeFormula($expr, Date_Time::DAY)) {
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------------- _checkNumericExpr
	/**
	 * Check an expression is numeric
	 * @param $expr string
	 * @return string
	 */
	private function _checkNumericExpr(&$expr)
	{
		if (is_numeric($expr) && (string)((int)$expr) == $expr) {
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------ _checkDateWildcardExpr
	/**
	 * Check an expression (part of a datetime) contains wildcards and correct it, if necessary
	 * @param &$expr string
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return boolean
	 */
	protected function _checkDateWildcardExpr(&$expr, $part)
	{
		$expr = str_replace(['*','?'],['%','_'], $expr);
		$nchar = ($part == Date_Time::YEAR ? 4 : 2);
		if ($c = preg_match_all("/^[0-9_%]{1,$nchar}$/", $expr)) {
			$this->_correctDateWildcardExpr($expr, $part);
			return true;
		}
		return false;
	}

	//---------------------------------------------------------------------- _correctDateWildcardExpr
	/** Correct a date expression containing SQL wildcard in order to build a Date string
	 * @param &$expr string
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return void
	 */
	protected function _correctDateWildcardExpr(&$expr, $part)
	{
		/** eg. for a month or day (or hour, minutes, seconds), it's simple since we have 2 chars only
		 * %% => __
		 * %  => __
		 * 1% => 1_
		 * %2 => _2
		 * _  => __
		 * So we simply have to replace % by _ and if a single _ then __
		 */
		if ($part != Date_Time::YEAR) {
			$expr = str_replace('%','_',$expr);
			if ($expr == '_') {
				$expr = '__';
			}
		}
		/** eg. for a year, it's a bit more complex. All possible combinations => correction
		 * %%%% => ____
		 * %%%  => ____
		 * %%   => ____
		 * %    => ____    use pattern #1#
	   *
		 * 2%%% => 2___
		 * 2%%  => 2___
		 * 2%   => 2___    use pattern #2#
		 *
		 * 20%% => 20__
		 * 20%  => 20__    use pattern #3#
		 *
		 * %%%6 => ___6
		 * %%6  => ___6
		 * %6   => ___6    use pattern #4#
		 *
		 * %%16 => __16
		 * %16  => __16    use pattern #5#
		 *
		 * 2%%6 => 2__6
		 * 2%6  => 2__6    use pattern #6#
		 *
		 * %016 => _016    direct replace % by _
		 * 2%16 => 2_16    direct replace % by _
		 * 20%6 => 20_6    direct replace % by _
		 * 201% => 201_    direct replace % by _
		 *
		 * %0%6 => _0_6    direct replace % by _
		 * %01% => _01_    direct replace % by _
		 * 2%1% => 2_1_    direct replace % by _
		 */
		static $patterns = [
			/* #1# */ '/^[%]{1,4}$/',
			/* #2# */ '/^([0-9_])[%]{1,3}$/',
			/* #3# */ '/^([0-9_][0-9_])[%]{1,2}$/',
			/* #4# */ '/^[%]{1,3}([0-9_])$/',
			/* #5# */ '/^[%]{1,2}([0-9_][0-9_])$/',
			/* #6# */ '/^([0-9_])[%]{1,2}([0-9_])$/'
		];
		static $replacements = [
			/* #1# */ '____',
			/* #2# */ '${1}___',
			/* #3# */ '${1}__',
			/* #4# */ '___${1}',
			/* #5# */ '__${1}',
			/* #6# */ '${1}__${2}'
		];
		$expr = preg_replace($patterns, $replacements, $expr);
		$expr = str_replace('%', '_', $expr);
	}

	//------------------------------------------------------------------------------- _computeFormula
	/** Compile a formula and compute value for a part of date
	 * @param &$expr string formula
	 * @param $part string Date_Time::DAY | Date_Time::MONTH | Date_Time::YEAR | Date_Time::HOUR | Date_Time::MINUTE | Date_Time::SECOND
	 * @return boolean true if formula found
	 */
	protected function _computeFormula(&$expr, $part)
	{
		$p = [
			Date_Time::YEAR   => '[yaYA]', // y+1 y-3 a+1 a-3
			Date_Time::MONTH  => '[mM]',
			Date_Time::DAY    => '[djDJ]',
			Date_Time::HOUR   => '[hH]',
			Date_Time::MINUTE => '[mM]',
			Date_Time::SECOND => '[sS]'
		];
		$pp = $p[$part];
		if (preg_match("/^ \\s* $pp \\s* (?:(?<sign>[-+]) \\s* (?<operand>\\d+))? \\s* $/x", $expr, $matches)) {
			/**
			 * Note: We take care to keep computed values as computed even if above limits
			 * (eg for a month >12 or <1) because we'll give result to mktime in order
			 * it may change year and/or day accordingly
			 * eg current month is 12 and formula is m+1 => mktime(0,0,0,20,13,2016) for 20/01/2017
			 */
			$f = [
				Date_Time::YEAR   => 'Y',
				Date_Time::MONTH  => 'm',
				Date_Time::DAY    => 'd',
				Date_Time::HOUR   => 'h',
				Date_Time::MINUTE => 'i',
				Date_Time::SECOND => 's'
			];
			$value = (int)$this->currentDateTime->format($f[$part]);
			if (isset($matches['sign']) && isset($matches['operand'])) {
				$sign = $matches['sign'];
				$operand = (int)($matches['operand']);
				$expr = (string)($sign == '+' ? $value + $operand : $value - $operand);
			} else {
				$expr = $value;
			}
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------------------- isEmptyWord
	/** Check if expression is an empty word
	 * @param $expr string
	 * @return boolean true if empty word
	 */
	protected function isEmptyWord($expr)
	{
		$word = preg_replace('/\s/','',$expr);
		if (in_array(Loc::rtr($word), ['empty', 'none', 'null'])) {
			return true;
		}
		return false;
	}

	//----------------------------------------------------------------------------- buildDateOrPeriod
	/**
	 * build the correct Dao object for given begin and end date according to what we want
	 * @param $date_begin   string
	 * @param $date_end     string
	 * @param $minmax
	 * @return Range|string
	 */
	protected function buildDateOrPeriod($date_begin, $date_end, $minmax)
	{
		if ($minmax == self::MIN_RANGE_VALUE) {
			$date = $date_begin;
		} elseif ($minmax == self::MAX_RANGE_VALUE) {
			$date = $date_end;
		} else {
			$date = new Range($date_begin, $date_end);
		}
		return $date;
	}

	//---------------------------------------------------------------------------------- padDateParts
	/**
	 * pad the date parts to have left leading 0
	 * @param $day     string|integer
	 * @param $month   string|integer
	 * @param $year    string|integer
	 * @return array
	 */
	protected function padDateParts($day, $month, $year)
	{
		$day = str_pad($day, 2, '0', STR_PAD_LEFT);
		$month = str_pad($month, 2, '0', STR_PAD_LEFT);
		$year = str_pad($year, 2, '0', STR_PAD_LEFT);
		return [$day, $month, $year];
	}

}
