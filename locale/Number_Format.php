<?php
namespace ITRocks\Framework\Locale;

/**
 * Number format locale features : changes number format to comply with user's locale configuration
 */
class Number_Format
{

	//------------------------------------------------------------------------- DECIMAL_MAXIMAL_COUNT
	const DECIMAL_MAXIMAL_COUNT = 'decimal_maximal_count';

	//------------------------------------------------------------------------- DECIMAL_MINIMAL_COUNT
	const DECIMAL_MINIMAL_COUNT = 'decimal_minimal_count';

	//----------------------------------------------------------------------------- DECIMAL_SEPARATOR
	const DECIMAL_SEPARATOR     = 'decimal_separator';

	//---------------------------------------------------------------------------- THOUSAND_SEPARATOR
	const THOUSAND_SEPARATOR    = 'thousand_separator';

	//------------------------------------------------------------------------ $decimal_maximal_count
	/**
	 * @var integer
	 */
	public $decimal_maximal_count = 4;

	//------------------------------------------------------------------------ $decimal_minimal_count
	/**
	 * @var integer
	 */
	public $decimal_minimal_count = 2;

	//---------------------------------------------------------------------------- $decimal_separator
	/**
	 * @var string
	 */
	public $decimal_separator = DOT;

	//--------------------------------------------------------------------------- $thousand_separator
	/**
	 * @var string
	 */
	public $thousand_separator = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters integer[]|string[]
	 */
	public function __construct($parameters = [])
	{
		foreach ($parameters as $key => $value) {
			$this->$key = $value;
		}
	}

	//------------------------------------------------------------------------------------ floatToIso
	/**
	 * @param $float string|null
	 * @return float|null
	 */
	public function floatToIso($float)
	{
		if (!isset($float) || !strlen($float)) {
			$result = null;
		}
		else {
			$result = str_replace(
				[$this->thousand_separator, $this->decimal_separator],
				['', DOT],
				$float
			);
			if (!isStrictNumeric($result)) {
				$result = $float;
			}
		}
		return $result;
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * @param $float                 float
	 * @param $decimal_minimal_count integer if set, overrides decimal minimal count
	 * @param $decimal_maximal_count integer if set, overrides decimal maximal count
	 * @return string
	 */
	public function floatToLocale(
		$float, $decimal_minimal_count = null, $decimal_maximal_count = null
	) {
		if (is_numeric($float)) {
			$float = number_format(
				$float,
				$decimal_maximal_count ?? $this->decimal_maximal_count,
				$this->decimal_separator,
				$this->thousand_separator
			);
			if ($position = strrpos($float, $this->decimal_separator)) {
				$decimals = strlen($float) - $position - 1;
				while (
					($float[$position + $decimals] === '0')
					&& ($decimals > ($decimal_minimal_count ?? $this->decimal_minimal_count))
				) {
					$decimals --;
				}
				$float = rtrim(substr($float, 0, $position + $decimals + 1), $this->decimal_separator);
			}
		}
		return $float;
	}

	//---------------------------------------------------------------------------------- integerToIso
	/**
	 * @param $integer string|null
	 * @return integer|null
	 */
	public function integerToIso($integer)
	{
		if (!isset($integer) || !strlen($integer)) {
			$result = null;
		}
		else {
			$result = str_replace($this->thousand_separator, '', $integer);
			if (!isStrictInteger($result)) {
				$result = $integer;
			}
		}
		return $result;
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * @param $integer integer
	 * @return string
	 */
	public function integerToLocale($integer)
	{
		return ($integer == (string)(integer)$integer)
			? number_format(intval($integer), 0, $this->decimal_separator, $this->thousand_separator)
			: $integer;
	}

}
