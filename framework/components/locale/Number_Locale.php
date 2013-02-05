<?php
namespace SAF\Framework;

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
	 * @param $float string
	 * @return float
	 */
	public function floatToIso($float)
	{
		return str_replace(
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
		if ($float == (string)(float)$float) {
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
	 * @param $integer string
	 * @return integer
	 */
	public function integerToIso($integer)
	{
		return str_replace($this->thousand_separator, "", $integer) + 0;
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
