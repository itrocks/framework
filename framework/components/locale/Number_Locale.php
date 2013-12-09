<?php
namespace SAF\Framework;

/**
 * Number locale features : changes number format to comply with user's locale configuration
 */
class Number_Locale implements Configurable
{

	//------------------------------------------------------------------------ $decimal_minimal_count
	/**
	 * @var integer
	 */
	public $decimal_minimal_count = 2;

	//------------------------------------------------------------------------ $decimal_maximal_count
	/**
	 * @var integer
	 */
	public $decimal_maximal_count = 4;

	//---------------------------------------------------------------------------- $decimal_separator
	/**
	 * @var string
	 */
	public $decimal_separator = ".";

	//--------------------------------------------------------------------------- $thousand_separator
	/**
	 * @var string
	 */
	public $thousand_separator = "";

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $parameters array
	 */
	public function __construct($parameters = null)
	{
		if (isset($parameters)) {
			foreach ($parameters as $key => $value) {
				$this->$key = $value;
			}
		}
	}

	//------------------------------------------------------------------------------------ floatToIso
	/**
	 * @param $float string|null
	 * @return float|null
	 */
	public function floatToIso($float)
	{
		return (!isset($float) || !strlen($float))
			? null
			: str_replace(
				array($this->thousand_separator, $this->decimal_separator),
				array("", "."),
				$float
			) + 0;
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * @param $float float
	 * @return string
	 */
	public function floatToLocale($float)
	{
		if (is_numeric($float)) {
			$float = number_format(
				$float, $this->decimal_maximal_count, $this->decimal_separator, $this->thousand_separator
			);
			if ($pos = strrpos($float, $this->decimal_separator)) {
				$decimals = strlen($float) - $pos - 1;
				while (($float[$pos + $decimals] == "0") && ($decimals > $this->decimal_minimal_count)) {
					$decimals--;
				}
				$float = substr($float, 0, $pos + $decimals + 1);
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
		return (!isset($integer) || !strlen($integer))
			? null
			: str_replace($this->thousand_separator, "", $integer) + 0;
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * @param $integer integer
	 * @return string
	 */
	public function integerToLocale($integer)
	{
		return ($integer == (string)(integer)$integer)
			? number_format($integer + 0, 0, $this->decimal_separator, $this->thousand_separator)
			: $integer;
	}

}
