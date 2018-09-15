<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Schedule\Hour_Range;

/**
 * A schedule trigger calculates if the action must be run from time factors
 *
 * @display_order name, hours, days_of_month, months, years, days_of_weeks
 */
class Schedule extends Trigger
{
	use Has_Name;

	//-------------------------------------------------------------------------------- $days_of_month
	/**
	 * @user hide_empty
	 * @var string
	 */
	public $days_of_month;

	//--------------------------------------------------------------------------------- $days_of_week
	/**
	 * @ordered_values
	 * @user hide_empty
	 * @values monday, tuesday, wednesday, thursday, friday, saturday, sunday
	 * @var string[]
	 */
	public $days_of_week;

	//---------------------------------------------------------------------------------- $hour_ranges
	/**
	 * @link Collection
	 * @user hide_empty
	 * @var Hour_Range[]
	 */
	public $hour_ranges;

	//---------------------------------------------------------------------------------------- $hours
	/**
	 * @user hide_empty
	 * @var string
	 */
	public $hours;

	//--------------------------------------------------------------------------------------- $months
	/**
	 * @user hide_empty
	 * @var string
	 */
	public $months;

	//---------------------------------------------------------------------------------------- $years
	/**
	 * @user hide_empty
	 * @var string
	 */
	public $years;

}
