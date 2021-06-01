<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Trigger\Schedule;

/**
 * Hour range
 *
 * @sort from, until, frequency
 * @store_name trigger_schedule_hour_ranges
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
	 * @min_value 0
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
	 * @min_value 0
	 * @regexp [0-2][0-9]:[0-5][0-9]([0-5][0-9])?
	 * @var string
	 */
	public $until;

	//------------------------------------------------------------------------------------- normalize
	/**
	 * Replace empty range limits from and to by their default values 00:00:00 and 23:59:59
	 * Complete with default minutes / seconds
	 *
	 * @param $until string @default 23:59:59
	 */
	public function normalize($until = '23:59:59')
	{
		if (!$this->from) {
			$this->from = '00:00:00';
		}
		elseif (strlen($this->from) < 8) {
			if (strlen($this->from) === 1) {
				$this->from = '0' . $this->from;
			}
			$this->from .= substr('00:00:00', strlen($this->from));
		}
		if (!$this->until) {
			$this->until = $until;
		}
		elseif (strlen($this->until) < 8) {
			if (strlen($this->until) === 1) {
				$this->until = '0' . $this->until;
			}
			$this->until .= substr($until, strlen($this->until));
		}
		if ($this->frequency && !$this->frequency_unit) {
			$this->frequency_unit = 'minutes';
		}
	}

}
