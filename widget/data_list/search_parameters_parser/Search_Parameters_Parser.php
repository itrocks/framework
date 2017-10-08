<?php
namespace ITRocks\Framework\Widget\Data_List;

use Exception;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Comparison;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Date;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Range;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Scalar;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Type_Boolean;
use ITRocks\Framework\Widget\Data_List\Search_Parameters_Parser\Words;

/**
 * Search parameters parser
 *
 * - Search grammar (and so algorithm):
 *
 * spaces are optionals
 * search       = orexpr
 * orexpr       = andexpr [, andexpr [...]]]
 * andexpr      = notexpr [& notexpr [...]]
 * notexpr      = ["!"]complexvalue
 * complexvalue = singlevalue
 * singlevalue  = emptyword | scalar
 * emptyword    = "empty" | "null" | localized equivalent
 * scalar       = sentence that may contains wildcards
 * wildcard     = "?" | "*" | "%" | "_"
 *
 * - Especially for Boolean :
 * singlevalue  = emptyword | booleanvalue
 * booleanvalue = booleanword
 *              | "0" | "1" | number != 0 (for true)
 * booleanword  = "no" | "yes" | "n" | "y" | "false" | "true"
 *
 * - Especially for Date_Time, Float, Integer, String fields :
 * complexvalue = range | singlevalue
 * range        = minrgnvalue "-" maxrngvalue
 *
 * < > <= >= Especially for Date_Time, Float, Integer, String fields
 * Compare value
 *
 * - Especially for Float, Integer, String fields :
 * minrngvalue  = scalar
 * maxrngvalue  = scalar
 *
 * - Especially for Date_Time
 * singlevalue  = dateperiod   (that will be updated to both a min and a max value of the period)
 * minrngvalue  = daterngvalue (that will be updated to its min value)
 * maxrngvalue  = daterngvalue (that will be updated to its max value)
 * daterngvalue = dateperiod without any wildcard
 * dateperiod   = dateword | emptyword | wildcard
 *              | [d]d/[m]m/yyyy
 *              | [m]m/yyyy | yyyy/[m]m (means from 01/mm/yyyy to 31!/mm/yyyy) 3-4 chars mandatory
 *                for yyyy
 *              | [d]d/[m]m         (means implicit current year)
 *              | yyyy              (means from 01/01/yyyy to 31/12/yyyy) 3-4 chars mandatory
 *              | [d]d              (means implicit current month and year)
 *              | "y" [+|-] integer (means from 01/01/yyyy to 31/12/yyyy)
 *              | "m" [+|-] integer (means from 01/mm/currentyear to 31!/mm/currentyear)
 *              | "d" [+|-] integer (means implicit current month and year)
 * dateword     = "current year" | "current month" | localized equivalent
 *              | "today" | "current day" | localized equivalent
 *              | "now" (means with current time?)
 * dd           = #[0-3?]?[0-9?]|*# | "d" (+|-) integer
 * mm           = #[0-1?]?[0-9?]|*# | "m" (+|-) integer
 * yyyy         = #[0-9?]{4}|*#     | "y" (+|-) integer //is it possible to check year about "*"
 *                only? we can not be sure this is a year!
 *
 * If there is any wildcard (*?) on a dd, mmm or yyyy, it will be converted to a LIKE search
 * Otherwise any date will be converted to a period from midnight to 23h59:59
 * For ranges, min date will be converted to midnight and maxdate to 23h59:59
 *
 * TODO naming conventions for properties : $property_name
 * TODO alphabetical order of the methods
 *
 * Parse user-input search strings to get valid search arrays in return
 */
class Search_Parameters_Parser
{

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

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, array $search = [])
	{
		$this->class  = new Reflection_Class($class_name);
		$this->search = $search;
		Date::initDates();
	}

	//-------------------------------------------------------------------------------------- applyAnd
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Logical
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

	//----------------------------------------------------------------------------- applyComplexValue
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return mixed        a range, dao func or scalar
	 * @throws Data_List_Exception
	 */
	protected function applyComplexValue($search_value, Reflection_Property $property)
	{
		if (Comparison::isComparison($search_value, $property) && Range::supportsRange($property)) {
			$search = Comparison::applyComparison($search_value, $property);
		}
		elseif (Range::isRange($search_value, $property) && Range::supportsRange($property)) {
			$search = Range::applyRange($search_value, $property);
		}
		else {
			$search = $this->applySingleValue($search_value, $property);
		}
		return $search;
	}

	//-------------------------------------------------------------------------------------- applyNot
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Logical
	 */
	protected function applyNot($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (substr(trim($search_value), 0, 1) === '!')) {
			$search_value = substr(trim($search_value), 1);
			$search       = $this->applyComplexValue($search_value, $property);
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

	//--------------------------------------------------------------------------------------- applyOr
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Logical
	 */
	protected function applyOr($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (strpos($search_value, ',') !== false)) {
			$or = [];
			foreach (explode(',', $search_value) as $search) {
				$or[] = $this->applyAnd($search, $property);
			}
			return Func::orOp($or);
		}
		else {
			$search = $this->applyAnd($search_value, $property);
			return $search;
		}
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
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// boolean type
			case Type::BOOLEAN: {
				if (($search = Words::applyEmptyWord($search_value)) !== false) {
					break;
				}
				$search = Type_Boolean::applyBooleanValue($search_value);
				break;
			}
			// Date_Time type
			case Date_Time::class: {
				$search = Date::applyDateValue($search_value);
				break;
			}
			// Float | Integer | String types
			//case in_array($type_string, [Type::FLOAT, Type::INTEGER, Type::STRING]): {
			default: {
				if (($search = Words::applyEmptyWord($search_value)) !== false) {
					break;
				}
				$search = Scalar::applyScalar($search_value, $property);
				break;
			}
		}
		if ($search === false) {
			throw new Data_List_Exception($search_value, Loc::tr('Error in expression'));
		}
		return $search;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @return array search-compatible search array
	 */
	public function parse()
	{
		$search   = $this->search;
		$to_unset = [];
		foreach ($search as $property_path => &$search_value) {
			$property = new Reflection_Property($this->class->name, $property_path);
			if (strlen($search_value)) {
				$this->parseField($search_value, $property);
				// if search has been transformed to empty string, we cancel search for this column
				if (is_string($search_value) && !strlen($search_value)) {
					$to_unset[] = $property_path;
				}
			}
		}
		foreach ($to_unset as $property_path) {
			unset($search[$property_path]);
		}
		return $search;
	}

	//------------------------------------------------------------------------------------ parseField
	/**
	 * @param $search_value string
	 * @param $property Reflection_Property
	 */
	protected function parseField(&$search_value, Reflection_Property $property)
	{
		try {
			$search_value = $this->applyOr($search_value, $property);
		}
		catch (Exception $exception) {
			$search_value = $exception;
		}
	}

}
