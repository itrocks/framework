<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Trigger\Schedule;

/**
 * Hour range
 */
class Hour_Range
{
	use Component;

	//------------------------------------------------------------------------------------ $frequency
	/**
	 * @max_value 36000
	 * @min_value 1
	 * @null
	 * @var integer
	 */
	public $frequency;

	//------------------------------------------------------------------------------- $frequency_unit
	/**
	 * @values seconds, minutes, hours, days, months, years
	 * @var string
	 */
	public $frequency_unit;

	//----------------------------------------------------------------------------------------- $from
	/**
	 * @max_length 8
	 * @max_value 23:59:59
	 * @min_length 5
	 * @min_value 00:00
	 * @regexp [0-2][0-9]:[0-5][0-9]([0-5][0-9])?
	 * @var string
	 */
	public $from;

	//------------------------------------------------------------------------------------- $schedule
	/**
	 * @composite
	 * @link Object
	 * @var Schedule
	 */
	public $schedule;

	//---------------------------------------------------------------------------------------- $until
	/**
	 * @max_length 8
	 * @max_value 23:59:59
	 * @min_length 5
	 * @min_value 00:00
	 * @regexp [0-2][0-9]:[0-5][0-9]([0-5][0-9])?
	 * @var string
	 */
	public $until;

}
