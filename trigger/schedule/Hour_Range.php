<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Feature\Validate\Property\Max_Value;
use ITRocks\Framework\Feature\Validate\Property\Min_Value;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Values;
use ITRocks\Framework\Trigger\Schedule;

/**
 * Hour range
 *
 * @sort from, until, frequency
 */
#[Store('trigger_schedule_hour_ranges')]
class Hour_Range
{
	use Component;

	//------------------------------------------------------------------------------------ $frequency
	#[Max_Value(36000), Min_Value(1)]
	public int $frequency = 0;

	//------------------------------------------------------------------------------- $frequency_unit
	#[Values('seconds, minutes, hours, days, months, years')]
	public string $frequency_unit = '';

	//----------------------------------------------------------------------------------------- $from
	/** @regexp [0-2][0-9]:[0-5][0-9]([0-5][0-9])? */
	#[Max_Length(8), Max_Value('23:59:59'), Min_Value('0')]
	public string $from = '';

	//------------------------------------------------------------------------------------- $schedule
	#[Composite]
	public Schedule $schedule;

	//---------------------------------------------------------------------------------------- $until
	/** @regexp [0-2][0-9]:[0-5][0-9]([0-5][0-9])? */
	#[Max_Length(8), Max_Value('23:59:59'), Min_Value(0)]
	public string $until = '';

	//------------------------------------------------------------------------------------- normalize
	/**
	 * Replace empty range limits from and to by their default values 00:00:00 and 23:59:59
	 * Complete with default minutes / seconds
	 */
	public function normalize(string $until = '23:59:59') : void
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
