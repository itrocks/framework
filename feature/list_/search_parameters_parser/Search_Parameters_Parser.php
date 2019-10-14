<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Dao\Func\Logical;
use ITRocks\Framework\Dao\Option;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Comparison;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Date;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Range;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Scalar;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Type_Boolean;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Wildcard;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Words;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Reflection\Annotation\Property\Values_Annotation;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;

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
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct($class_name, array $search = [])
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$this->class  = new Reflection_Class($class_name);
		$this->search = $search;
		Date::initDates();
	}

	//-------------------------------------------------------------------------------------- applyAnd
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Logical
	 * @throws Exception
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
	 * @throws Exception
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
	 * @throws Exception
	 */
	protected function applyNot($search_value, Reflection_Property $property)
	{
		if (is_string($search_value) && (substr(trim($search_value), 0, 1) === '!')) {
			$search_value = substr(trim($search_value), 1);
			$search       = $this->applyComplexValue($search_value, $property);
			if ($search instanceof Func\Negate) {
				$search->negate();
			}
			else {
				$search = Func::notEqual($search);
			}
		}
		else {
			$search = $this->applyComplexValue($search_value, $property);
		}
		return $search;
	}

	//--------------------------------------------------------------------------------------- applyOr
	/**
	 * @param $search_value string
	 * @param $property     Reflection_Property
	 * @return Logical
	 * @throws Exception
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
	 * @throws Exception
	 */
	protected function applySingleValue($search_value, Reflection_Property $property)
	{
		$type_string = $property->getType()->asString();
		switch ($type_string) {
			// boolean type
			case Type::BOOLEAN: {
				if ($search = Words::applyWordMeaningEmpty($search_value, $property)) {
					break;
				}
				$search = Type_Boolean::applyBooleanValue($search_value);
				break;
			}
			// Date_Time type
			case Date_Time::class: {
				$search = Date::applyDateValue($search_value, $property);
				break;
			}
			// String types with @values : translate
			case Type::STRING:
				/** @noinspection PhpMissingBreakStatementInspection */
			case Type::STRING_ARRAY: {
				$property_values = Values_Annotation::of($property)->values();
				if ($property_values || ($property->getAnnotation('translate')->value === 'common')) {
					if (trim($search_value) === '') {
						$search = Func::equal($search_value);
						break;
					}
					$values = null;
					foreach ($property_values as $value) {
						$values[] = Names::propertyToDisplay($value);
					}
					$reverse_translations = Loc::rtr(
						$search_value, $property->final_class, $property->name, $values
					);
					if (!$reverse_translations) {
						$reverse_translations = $search_value;
					}
					if (!$property_values) {
						if ($reverse_translations === Translator::TOO_MANY_RESULTS_MATCH_YOUR_INPUT) {
							$reverse_translations = $search_value;
						}
						else {
							if (!is_array($reverse_translations)) {
								$reverse_translations = [$reverse_translations];
							}
							$reverse_translations[] = $search_value;
						}
					}
					if (!is_array($reverse_translations)) {
						$reverse_translations = [$reverse_translations];
					}
					// to improve summary if no wildcard, no empty word, do a IN for many values
					$has_empty_word = false;
					$has_wildcard   = false;
					foreach ($reverse_translations as $value) {
						if (Words::applyWordMeaningEmpty($value, $property)) {
							$has_empty_word = true;
						}
						if (Wildcard::hasWildcard($value)) {
							$has_wildcard = true;
						}
					}
					if ($has_empty_word || $has_wildcard) {
						$searches = [];
						foreach ($reverse_translations as $value) {
							if (!($search = Words::applyWordMeaningEmpty($value, $property))) {
								if (Wildcard::hasWildcard($value)) {
									$search = Scalar::applyScalar($value);
								}
								else {
									if ($property_values) {
										$value = Names::displayToProperty($value);
									}
									$search = ($type_string == Type::STRING)
										? Func::equal($value)
										: Func::inSet($value);
								}
							}
							$searches[] = $search;
						}
						$search = (count($searches) > 1) ? Func::orOp($searches) : reset($searches);
					}
					else {
						if ($property_values) {
							foreach ($reverse_translations as &$value) {
								$value = Names::displayToProperty($value);
							}
						}
						$search = (count($reverse_translations) > 1)
							? Func::in($reverse_translations)
							: (($type_string == Type::STRING)
								? Func::equal(reset($reverse_translations))
								: Func::inSet(reset($reverse_translations))
							);
					}
					break;
				}
				// without @values : let it continue to 'default' in order to apply the 'default' process
			}
			// Float | Integer | String types without @values
			// case Type::FLOAT: case Type::INTEGER: case Type::STRING: case Type::STRING_ARRAY:
			default: {
				if (!($search = Words::applyWordMeaningEmpty($search_value, $property))) {
					$search = Scalar::applyScalar($search_value);
				}
				break;
			}
		}
		if ($search === false) {
			throw new Exception($search_value, Loc::tr('Error in expression'));
		}
		return $search;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return array search-compatible search array
	 */
	public function parse()
	{
		$search   = $this->search;
		$to_unset = [];
		foreach ($search as $property_path => &$search_value) {
			/** @noinspection PhpUnhandledExceptionInspection property path must be valid */
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
