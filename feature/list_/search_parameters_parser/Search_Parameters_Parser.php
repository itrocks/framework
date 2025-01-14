<?php
namespace ITRocks\Framework\Feature\List_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Date;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Range;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Scalar;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Type_Boolean;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Wildcard;
use ITRocks\Framework\Feature\List_\Search_Parameters_Parser\Words;
use ITRocks\Framework\Locale;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Locale\Translator;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Reflection\Type;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ReflectionException;

/**
 * Search parameters parser
 *
 * - Search grammar (and so algorithm) :
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
 *              | "0" | "1" | number !== 0 (for true)
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
 * dateword     = "current year" | "year" | "current month" | "month" | localized equivalent
 *              | "today" | "current day" | localized equivalent
 *              | "yesterday" | "tomorrow" | localized equivalent
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
	public Reflection_Class $class;

	//--------------------------------------------------------------------------------------- $search
	/**
	 * @var array
	 */
	public array $search;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $search     array user-input search string
	 */
	public function __construct(string $class_name, array $search = [])
	{
		/** @noinspection PhpUnhandledExceptionInspection $class_name must be valid */
		$this->class  = new Reflection_Class($class_name);
		$this->search = $search;
		Date::initDates();
	}

	//-------------------------------------------------------------------------------------- applyAnd
	/**
	 * @param $search_value string
	 * @param $property     ?Reflection_Property
	 * @return Func\Comparison|Func\Logical|Func\Range|string
	 * @throws Exception
	 */
	protected function applyAnd(string $search_value, ?Reflection_Property $property)
		: Func\Comparison|Func\Logical|Func\Range|string
	{
		if (!str_contains($search_value, '&')) {
			return $this->applyNot($search_value, $property);
		}
		$and = [];
		foreach (explode('&', $search_value) as $search) {
			$and[] = $this->applyNot($search, $property);
		}
		return Func::andOp($and);
	}

	//------------------------------------------------------------------------------- applyComparison
	/**
	 * Apply a Comparison expression on search string. The Comparison is supposed to exist !
	 *
	 * @param $expression string
	 * @param $property   ?Reflection_Property
	 * @return ?Func\Comparison
	 * @throws Exception
	 */
	protected function applyComparison(string $expression, ?Reflection_Property $property)
		: ?Func\Comparison
	{
		$comparison = null;
		$expression = ltrim($expression);
		if (in_array($sign = substr($expression, 0, 2), ['<=', '>=', '<>'])) {
			$comparison = new Func\Comparison($sign, ltrim(substr($expression, 2)));
		}
		elseif (in_array($sign = substr($expression, 0, 1), ['<', '>', '='])) {
			$comparison = new Func\Comparison($sign, ltrim(substr($expression, 1)));
		}
		if ($comparison && $property?->getType()->isDateTime()) {
			$applied = $this->applyDateValue($comparison->than_value, $property);
			if ($applied instanceof Func\Range) {
				switch ($comparison->sign) {
					case '>=':
					case '<':
						$comparison->than_value = $applied->from;
						break;
					case '<=':
					case '>':
						$comparison->than_value = $applied->to;
						break;
					case '<>':
						$comparison = Func::andOp(
							[new Func\Comparison('<', $applied->from), new Func\Comparison('>', $applied->to)]
						);
						break;
					case '=':
						$comparison = Func::andOp(
							[new Func\Comparison('>=', $applied->from), new Func\Comparison('<=', $applied->to)]
						);
						break;
				}
			}
			else {
				$comparison->than_value = $applied;
			}
		}
		return $comparison;
	}

	//----------------------------------------------------------------------------- applyComplexValue
	/**
	 * @param $search_value string
	 * @param $property     ?Reflection_Property
	 * @return mixed        a range, dao func or scalar
	 * @throws Exception
	 */
	protected function applyComplexValue(string $search_value, ?Reflection_Property $property) : mixed
	{
		$search = $this->applyComparison($search_value, $property);
		if ($search) {
			return $search;
		}
		return (Range::isRange($search_value, $property) && Range::supportsRange($property))
			? Range::applyRange($search_value, $property)
			: $this->applySingleValue($search_value, $property);
	}

	//-------------------------------------------------------------------------------- applyDateValue
	/**
	 * @param $date_time string locale formatted date-time
	 * @param $property  ?Reflection_Property
	 * @return Func\Comparison|Func\Logical|Func\Range|string
	 * @throws Exception
	 */
	protected function applyDateValue(string $date_time, ?Reflection_Property $property)
		: Func\Comparison|Func\Logical|Func\Range|string
	{
		return Date::applyDateValue($date_time, $property);
	}

	//-------------------------------------------------------------------------------------- applyNot
	/**
	 * @param $search_value string
	 * @param $property     ?Reflection_Property
	 * @return Func\Comparison|Func\Logical|Func\Range|string
	 * @throws Exception
	 */
	protected function applyNot(string $search_value, ?Reflection_Property $property)
		: Func\Comparison|Func\Logical|Func\Range|string
	{
		if (!str_starts_with(trim($search_value), '!')) {
			return $this->applyComplexValue($search_value, $property);
		}
		$search_value = substr(trim($search_value), 1);
		$search       = $this->applyComplexValue($search_value, $property);
		if ($search instanceof Func\Negate) {
			$search->negate();
		}
		else {
			$search = Func::notEqual($search);
		}
		return $search;
	}

	//--------------------------------------------------------------------------------------- applyOr
	/**
	 * @param $search_value string
	 * @param $property     ?Reflection_Property
	 * @return Func\Comparison|Func\Logical|Func\Range|string
	 * @throws Exception
	 */
	protected function applyOr(string $search_value, ?Reflection_Property $property)
		: Func\Comparison|Func\Logical|Func\Range|string
	{
		if (!str_contains($search_value, ',')) {
			return $this->applyAnd($search_value, $property);
		}
		if ($property && $property->getType()->isFloat()) {
			$number_format     = Locale::current()->number_format;
			$is_thousand_float = ($number_format->thousand_separator === ',');
			if ($is_thousand_float) {
				return $this->applyAnd($search_value, $property);
			}
			$is_decimal_float = ($number_format->decimal_separator === ',');
			if ($is_decimal_float && !str_contains($search_value, DOT)) {
				$decimal  = true;
				$position = -1;
				while (($position = strpos($search_value, ',', $position + 1)) !== false) {
					if ($decimal) {
						$search_value[$position] = DOT;
					}
					$decimal = !$decimal;
				}
			}
			if (!str_contains($search_value, ',')) {
				return $this->applyAnd($search_value, $property);
			}
		}
		$or = [];
		foreach (explode(',', $search_value) as $search) {
			$or[] = $this->applyAnd($search, $property);
		}
		return Func::orOp($or);
	}

	//------------------------------------------------------------------------------ applySingleValue
	/**
	 * @param $search_value string
	 * @param $property     ?Reflection_Property
	 * @return mixed
	 * @throws Exception
	 */
	protected function applySingleValue(string $search_value, ?Reflection_Property $property) : mixed
	{
		$type_string = $property ? $property->getType()->asString() : new Type(Type::STRING);
		switch ($type_string) {
			// boolean type
			case Type::BOOLEAN:
			case Type::FALSE:
			case Type::TRUE:
				$search = Words::applyWordMeaningEmpty($search_value, $property)
					?: Type_Boolean::applyBooleanValue($search_value);
				break;
			// Date_Time type
			case Date_Time::class:
				$search = $this->applyDateValue($search_value, $property);
				break;
			// String types with #Values : translate
			case Type::STRING: /** @noinspection PhpMissingBreakStatementInspection */
			case Type::STRING_ARRAY:
				$property_values = ($property ? Values::of($property)?->values : null) ?: [];
				if (
					$property_values
					|| ($property && ($property->getAnnotation('translate')->value === 'common'))
				) {
					if (trim($search_value) === '') {
						$search = Func::equal($search_value);
						break;
					}
					$values = [];
					foreach ($property_values as $value) {
						$values[] = Names::propertyToDisplay($value);
					}
					$reverse_translations = Loc::rtr(
						$search_value,
						Builder::current()->sourceClassName($property->final_class),
						$property->name,
						$values
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
									$search = ($type_string === Type::STRING)
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
							: (
								($type_string === Type::STRING)
									? Func::equal(reset($reverse_translations))
									: Func::inSet($reverse_translations)
							);
					}
					break;
				}
			default:
				// without #Values : let it continue to 'default' in order to apply the 'default' process
				// Float | Integer | String types without #Values
				// case Type::FLOAT: case Type::INTEGER: case Type::STRING: case Type::STRING_ARRAY:
				$search = Words::applyWordMeaningEmpty($search_value, $property)
					?: Scalar::applyScalar($search_value);
		}
		if ($search === false) {
			throw new Exception($search_value, Loc::tr('Error in expression'));
		}
		return $search;
	}

	//----------------------------------------------------------------------------------------- parse
	/**
	 * @return array search-compatible search array
	 */
	public function parse() : array
	{
		$search = $this->search;
		$this->parseArray($search);
		return $search;
	}

	//------------------------------------------------------------------------------------ parseArray
	/**
	 * @noinspection PhpDocMissingThrowsInspection Don't care
	 * @param $search_values    array An array of search values
	 * @param $ignore_exception bool
	 */
	public function parseArray(array &$search_values, bool $ignore_exception = false) : void
	{
		foreach ($search_values as $property_path => &$search_value) {
			if ($search_value instanceof Func\Logical) {
				$this->parseArray($search_value->arguments, $ignore_exception || $search_value->isOr());
				foreach ($search_value->arguments as $sub_value) {
					if ($sub_value instanceof Exception) {
						$search_value = $sub_value;
					}
				}
				continue;
			}
			// property path can be an Expressions::MARKER or 'representative' view field name
			try {
				$property = new Reflection_Property($this->class->name, $property_path);
			}
			catch (ReflectionException) {
				$property = null;
			}
			if ($search_value === '') {
				return;
			}
			$this->parseField($search_value, $property);
			if ($ignore_exception && ($search_value instanceof Exception)) {
				$search_value = '';
			}
			// if search has been transformed to empty string, we cancel search for this column
			if ($search_value === '') {
				unset($search_values[$property_path]);
			}
		}
	}

	//------------------------------------------------------------------------------------ parseField

	/**
	 * @param $search_value string
	 * @param $property ?Reflection_Property
	 */
	protected function parseField(string &$search_value, ?Reflection_Property $property) : void
	{
		try {
			$search_value = $this->applyOr($search_value, $property);
		}
		catch (Exception $exception) {
			$search_value = $exception;
		}
	}

}
