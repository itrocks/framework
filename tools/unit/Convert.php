<?php
namespace ITRocks\Framework\Tools\Unit;

use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Tools\Unit;

/**
 * Units converter
 *
 * @see Unit
 */
class Convert
{

	//----------------------------------------------------------------------------------------- ALIAS
	const ALIAS = [
		'fromSeconds'      => 'from',
		'fromHoursMinutes' => 'fromDate',
		'fromHours'        => 'fromDate'
	];

	//----------------------------------------------------------------------- $options keys constants
	const DAY_HALF     = 'day_half';
	const HOURS_IN_DAY = 'hours_in_day';

	//-------------------------------------------------------------------------- $convert_method_name
	/**
	 * @var string
	 */
	protected $convert_method_name;

	//----------------------------------------------------------------------------- $from_method_name
	/**
	 * @var string
	 */
	protected $from_method_name;

	//------------------------------------------------------------------------------------ $from_unit
	/**
	 * Please call findMethod() after changing it, or it won't apply
	 *
	 * @var string
	 */
	public $from_unit;

	//-------------------------------------------------------------------------------------- $options
	/**
	 * @var integer[]
	 */
	public $options = [
		self::DAY_HALF     => true,
		self::HOURS_IN_DAY => 7.8
	];

	//------------------------------------------------------------------------------- $to_method_name
	/**
	 * @var string
	 */
	protected $to_method_name;

	//-------------------------------------------------------------------------------------- $to_unit
	/**
	 * Please call findMethod() after changing it, or it won't apply
	 *
	 * @var string
	 */
	public $to_unit;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $from_unit string @values Unit::const
	 * @param $to_unit   string @values Unit::const
	 */
	public function __construct($from_unit, $to_unit)
	{
		$this->setUnits($from_unit, $to_unit);
	}

	//--------------------------------------------------------------------------------------- convert
	/**
	 * Returns the value, converted to the given unit
	 *
	 * @param $value float|integer|string
	 * @return integer|float|string|null
	 */
	public function convert($value)
	{
		if ($this->convert_method_name) {
			return $this->{$this->convert_method_name}($value);
		}
		if ($this->from_method_name && $this->to_method_name) {
			return $this->{$this->to_method_name}($this->{$this->from_method_name}($value));
		}
		return null;
	}

	//------------------------------------------------------------------------------------ findMethod
	/**
	 * @return boolean true if a method is available to convert from $from_unit to $to_unit
	 */
	protected function findMethod()
	{
		$convert_method_name = Names::propertyToMethod($this->from_unit . '_to_' . $this->to_unit);
		$from_method_name    = Names::propertyToMethod('from_' . $this->from_unit);
		$to_method_name      = Names::propertyToMethod('to_' . $this->to_unit);

		if (isset(self::ALIAS[$convert_method_name])) {
			$convert_method_name = self::ALIAS[$convert_method_name];
		}
		if (isset(self::ALIAS[$from_method_name])) {
			$from_method_name = self::ALIAS[$from_method_name];
		}
		if (isset(self::ALIAS[$to_method_name])) {
			$to_method_name = self::ALIAS[$to_method_name];
		}

		$this->convert_method_name = null;
		$this->from_method_name    = null;
		$this->to_method_name      = null;
		if (method_exists($this, $from_method_name) && method_exists($this, $to_method_name)) {
			$this->from_method_name    = $from_method_name;
			$this->to_method_name      = $to_method_name;
			return true;
		}
		elseif (method_exists($this, $convert_method_name)) {
			$this->convert_method_name = $convert_method_name;
			return true;
		}
		trigger_error(
			"Conversion from $this->from_unit to $this->to_unit not implemented",
			E_USER_WARNING
		);
		return false;
	}

	//------------------------------------------------------------------------------------------ from
	/**
	 * @param $value float|integer
	 * @return integer
	 */
	protected function from($value)
	{
		return $value;
	}

	//-------------------------------------------------------------------------------------- setUnits
	/**
	 * @param $from_unit string @values Unit::const
	 * @param $to_unit   string @values Unit::const
	 */
	public function setUnits($from_unit, $to_unit)
	{
		$this->from_unit = $from_unit;
		$this->to_unit   = $to_unit;
		$this->findMethod();
	}

	//---------------------------------------------------------------------------------------- toDays
	/**
	 * @param $value float|integer
	 * @return float|integer
	 */
	protected function toDays($value)
	{
		return ($this->options[self::DAY_HALF])
			? (round(2 * $value / $this->options[self::HOURS_IN_DAY] / 3600) / 2)
			: round($value / $this->options[self::HOURS_IN_DAY] / 3600);
	}

	//-------------------------------------------------------------------------------- toHoursMinutes
	/**
	 * @param $value float|integer
	 * @return string
	 */
	protected function toHoursMinutes($value)
	{
		return sprintf(
			'%s:%02s',
			floor($value / 3600),
			round($value / 60) % 60
		);
	}

	//------------------------------------------------------------------------- toHoursMinutesSeconds
	/**
	 * @param $value float|integer
	 * @return string
	 */
	protected function toHoursMinutesSeconds($value)
	{
		return sprintf(
			'%s:%02s:%02s',
			floor($value / 3600),
			floor($value / 60) % 60,
			round($value) % 60
		);
	}

}
