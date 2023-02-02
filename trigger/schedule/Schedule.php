<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Mapper\Comparator;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Component;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Action\Status;
use ITRocks\Framework\Trigger\Schedule\Hour_Range;

/**
 * A schedule trigger calculates if the action must be run from time factors
 *
 * @after_write calculateActionsNextLaunchDateTime
 * @display_order name, hours, days_of_month, months, years, days_of_weeks
 * @override actions @set_store_name trigger_schedule_actions @var Schedule\Action[]
 * @property Schedule\Action[] actions
 */
#[Store('trigger_schedules')]
class Schedule extends Trigger
{

	//---------------------------------------------------------------------------------- DAYS_OF_WEEK
	const DAYS_OF_WEEK = [
		'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'
	];

	//-------------------------------------------------------------------------------- $days_of_month
	/**
	 * @user hide_empty
	 */
	public string $days_of_month = '';

	//--------------------------------------------------------------------------------- $days_of_week
	/**
	 * @ordered_values
	 * @user hide_empty
	 * @values self::DAYS_OF_WEEK
	 * @var string[]
	 */
	public array $days_of_week = [];

	//---------------------------------------------------------------------------------- $hour_ranges
	/**
	 * @user hide_empty
	 * @var Hour_Range[]
	 */
	#[Component]
	public array $hour_ranges = [];

	//---------------------------------------------------------------------------------------- $hours
	/**
	 * @user hide_empty
	 */
	public string $hours = '';

	//--------------------------------------------------------------------------------------- $months
	/**
	 * @user hide_empty
	 */
	public string $months = '';

	//---------------------------------------------------------------------------------------- $years
	/**
	 * @user hide_empty
	 */
	public string $years = '';

	//------------------------------------------------------------ calculateActionsNextLaunchDateTime
	/**
	 * Must be called on actions that are already written into database (after write)
	 *
	 * @noinspection PhpUnused @after_write
	 */
	public function calculateActionsNextLaunchDateTime() : void
	{
		Dao::begin();
		$now = Date_Time::now();
		foreach ($this->actions as $action) {
			$action->status = Status::STATIC;
			$action->next(($action->last && $action->last->isEmpty()) ? $now : $action->last);
		}
		Dao::commit();
	}

	//-------------------------------------------------------------------------------- getDaysOfMonth
	/**
	 * @return string[] @max_value 31 @min_value 01
	 */
	public function getDaysOfMonth() : array
	{
		return $this->rangesListToArray($this->days_of_month, 31);
	}

	//------------------------------------------------------------------------- getExtendedHourRanges
	/**
	 * Return hour ranges and hours changed to hour ranges
	 *
	 * @return Hour_Range[]
	 */
	public function getExtendedHourRanges() : array
	{
		$hour_ranges = array_merge($this->hour_ranges, $this->hourRangesListToRanges($this->hours));
		if ($hour_ranges) {
			foreach ($hour_ranges as $hour_range) {
				$hour_range->normalize();
			}
			(new Comparator(Hour_Range::class))->sort($hour_ranges);
		}
		else {
			$hour_range                 = new Hour_Range();
			$hour_range->frequency      = 1;
			$hour_range->frequency_unit = 'days';
			$hour_range->from           = '00:00:00';
			$hour_range->until          = '23:59:59';
			$hour_ranges                = [$hour_range];
		}
		return $hour_ranges;
	}

	//------------------------------------------------------------------------------------- getMonths
	/**
	 * @return string[] @max_value 12 @min_value 01
	 */
	public function getMonths() : array
	{
		return $this->rangesListToArray($this->months, 12);
	}

	//-------------------------------------------------------------------------- getNumericDaysOfWeek
	/**
	 * @return integer[] @max_value 7 @min_value 1
	 */
	public function getNumericDaysOfWeek() : array
	{
		$days        = [];
		$day_numbers = array_flip(self::DAYS_OF_WEEK);
		foreach ($this->days_of_week as $day) {
			$days[] = $day_numbers[$day] + 1;
		}
		return $days;
	}

	//-------------------------------------------------------------------------------------- getYears
	/**
	 * @return string[] @max_value 2999 @min_value 2000
	 */
	public function getYears() : array
	{
		return $this->rangesListToArray($this->years, 2999);
	}

	//------------------------------------------------------------------------ hourRangesListToRanges
	/**
	 * @param $values_string string
	 * @return Hour_Range[]
	 */
	protected function hourRangesListToRanges(string $values_string) : array
	{
		$list        = $values_string ? explode(',', str_replace(SP, '', $values_string)) : [];
		$hour_ranges = [];

		foreach ($list as $element) {
			$hour_range = new Hour_Range();
			if (!str_contains($element, '-')) {
				$hour_range->from = $hour_range->until = $element;
			}
			else {
				[$hour_range->from, $hour_range->until] = explode('-', $element);
			}
			$hour_range->schedule = $this;
			$hour_range->normalize('00:00:00');
			$hour_ranges[] = $hour_range;
		}

		return $hour_ranges;
	}

	//----------------------------------------------------------------------------- rangesListToArray
	/**
	 * @param $values_string string @example '1,2,5-9,7,15' => [01, 02, 05, 06, 07, 08, 09, 15]
	 * @param $max_value     integer
	 * @return string[]
	 */
	protected function rangesListToArray(string $values_string, int $max_value) : array
	{
		$list       = $values_string ? explode(',', str_replace(SP, '', $values_string)) : [];
		$max_length = strlen($max_value);
		$values     = [];

		foreach ($list as $element) {
			if (!str_contains($element, '-')) {
				$value          = str_pad($element, $max_length, '0', STR_PAD_LEFT);
				$values[$value] = $value;
			}
			else {
				[$start, $stop] = explode('-', $element);
				$start = intval($start) ?: 1;
				$stop  = intval($stop)  ?: $max_value;
				for ($element = $start; $element <= $stop; $element++) {
					$value          = str_pad($element, $max_length, '0', STR_PAD_LEFT);
					$values[$value] = $value;
				}
			}
		}

		sort($values);
		return $values;
	}

}
