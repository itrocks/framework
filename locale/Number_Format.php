<?php
namespace ITRocks\Framework\Locale;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Call_Stack;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View\User_Error_Exception;

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
	public int $decimal_maximal_count = 4;

	//------------------------------------------------------------------------ $decimal_minimal_count
	/**
	 * @var integer
	 */
	public int $decimal_minimal_count = 2;

	//---------------------------------------------------------------------------- $decimal_separator
	/**
	 * @var string
	 */
	public string $decimal_separator = DOT;

	//--------------------------------------------------------------------------- $thousand_separator
	/**
	 * @var string
	 */
	public string $thousand_separator = '';

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
	 * @param $float ?string
	 * @return ?float
	 * @throws User_Error_Exception
	 */
	public function floatToIso(?string $float) : ?float
	{
		if (!(isset($float) && strlen($float))) {
			return null;
		}
		$result = str_replace([$this->thousand_separator, $this->decimal_separator], ['', DOT], $float);
		if (!is_numeric($result)) {
			$this->throwUserException('float', $float);
		}
		return floatval($result);
	}

	//--------------------------------------------------------------------------------- floatToLocale
	/**
	 * @param $float                 ?float
	 * @param $decimal_minimal_count integer|null if set, overrides decimal minimal count
	 * @param $decimal_maximal_count integer|null if set, overrides decimal maximal count
	 * @return string
	 */
	public function floatToLocale(
		?float $float, int $decimal_minimal_count = null, int $decimal_maximal_count = null
	) {
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
		return $float;
	}

	//---------------------------------------------------------------------------------- integerToIso
	/**
	 * @param $integer ?string
	 * @return ?integer
	 * @throws User_Error_Exception
	 */
	public function integerToIso(?string $integer) : ?int
	{
		if (!(isset($integer) && strlen($integer))) {
			return null;
		}
		$result = str_replace($this->thousand_separator, '', $integer);
		if (!isStrictInteger($result)) {
			$this->throwUserException('integer', $integer);
		}
		return intval($result);
	}

	//------------------------------------------------------------------------------- integerToLocale
	/**
	 * @param $integer ?integer
	 * @return string
	 */
	public function integerToLocale(?int $integer) : string
	{
		return number_format($integer, 0, $this->decimal_separator, $this->thousand_separator);
	}

	//---------------------------------------------------------------------------- throwUserException
	/**
	 * @param $type  string
	 * @param $value string
	 * @throws User_Error_Exception
	 */
	protected function throwUserException(string $type, string $value)
	{
		$property      = (new Call_Stack)->getArgumentsValue(['property', 'property_name'], true);
		$property_name = ($property instanceof Reflection_Property) ? $property->name : $property;
		$message       = Loc::tr(":value is not a valid $type", Loc::replace(['value' => $value]));
		if (is_string($property_name)) {
			$message = Loc::tr(
					'Invalid :property_name',
					Loc::replace(['property_name' => Loc::tr(Names::propertyToDisplay($property_name))])
				)
				. ' : ' . $message;
		}
		throw new User_Error_Exception($message);
	}

}
