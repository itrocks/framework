<?php
namespace ITRocks\Framework\Trigger\Schedule;

use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Schedule;

/**
 * Calculates the date-time of the next triggering of the schedule
 */
class Next_Calculation
{

	//----------------------------------------------------------------------------------------- $date
	/**
	 * @var Date_Time
	 */
	protected Date_Time $date;

	//------------------------------------------------------------------------------------- $schedule
	/**
	 * @var Schedule
	 */
	protected Schedule $schedule;

	//------------------------------------------------------------------------------------------ next
	/**
	 * Calculate the next schedule, after the given datetime
	 *
	 * @param $schedule Schedule
	 * @param $date     Date_Time|null @default Date_Time::now()
	 * @return Date_Time
	 */
	public function next(Schedule $schedule, Date_Time $date = null)
	{
		$this->date     = ($date ?: Date_Time::now())->add(1, Date_Time::SECOND);
		$this->schedule = $schedule;
		$this->nextTime(true);
		return $this->date;
	}

	//-------------------------------------------------------------------------------- nextDayOfMonth
	/**
	 * Calculate next allowed day of month
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return boolean true if date changed, else false
	 */
	protected function nextDayOfMonth() : bool
	{
		if ($this->date->isMax()) {
			return false;
		}
		$forward = $this->nextMonth();
		if (!trim($this->schedule->days_of_month)) {
			return $forward;
		}
		[$date_year, $date_month, $date_day] = explode('-', $this->date->format('Y-m-d'));
		$days_of_month = $this->schedule->getDaysOfMonth();
		// last day exceeded : next month
		if ($date_day > end($days_of_month)) {
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(
				0, 0, 0, intval($date_month) + 1, reset($days_of_month), $date_year
			));
			$this->nextDayOfMonth();
			return true;
		}
		// current or next available day of month
		if (!in_array($date_day, $days_of_month)) {
			foreach ($days_of_month as $day_of_month) {
				if ($day_of_month > $date_day) {
					$date_day = $day_of_month;
					break;
				}
			}
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, $date_month, $date_day, $date_year));
			return true;
		}
		return false;
	}

	//--------------------------------------------------------------------------------- nextDayOfWeek
	/**
	 * Calculate next allowed day of week
	 *
	 * Once the next day of month / month / year has been calculated, the next available day of week
	 * will be used, without filtering by this day of month or month or year : day of week is in
	 * 'execute late' mode
	 *
	 * @return boolean true if date changed, else false
	 */
	protected function nextDayOfWeek() : bool
	{
		if ($this->date->isMax()) {
			return false;
		}
		$forward = $this->nextDayOfMonth();
		if (!$this->schedule->days_of_week) {
			return $forward;
		}
		$date_day_of_week = $this->date->format(Date_Time::DAY_OF_WEEK_ISO);
		$days_of_week     = $this->schedule->getNumericDaysOfWeek();
		// current or next available day of week
		if (!in_array($date_day_of_week, $days_of_week)) {
			$this->date = $this->date->toBeginOf(Date_Time::DAY);
			do {
				$this->date->add(1);
				$date_day_of_week ++;
				if ($date_day_of_week > 7) {
					$date_day_of_week -= 7;
				}
			}
			while (!in_array($date_day_of_week, $days_of_week));
			return true;
		}
		return false;
	}

	//------------------------------------------------------------------------------------- nextMonth
	/**
	 * Calculate next allowed month
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return boolean true if date changed, else false
	 */
	protected function nextMonth() : bool
	{
		if ($this->date->isMax()) {
			return false;
		}
		$forward = $this->nextYear();
		if (!trim($this->schedule->months)) {
			return $forward;
		}
		[$date_year, $date_month] = explode('-', $this->date->format('Y-m'));
		$months = $this->schedule->getMonths();
		// last month exceeded : next year
		if ($date_month > end($months)) {
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, reset($months), 1, intval($date_year) + 1));
			$this->nextMonth();
			return true;
		}
		// current or next available month
		if (!in_array($date_month, $months)) {
			foreach ($months as $month) {
				if ($month > $date_month) {
					$date_month = $month;
					break;
				}
			}
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, $date_month, 1, $date_year));
			return true;
		}
		return false;
	}

	//-------------------------------------------------------------------------------------- nextTime
	/**
	 * Calculate next time in the day
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $forward boolean
	 * @return boolean true if date changed, else false
	 */
	protected function nextTime(bool $forward = false) : bool
	{
		if ($this->date->isMax()) {
			return false;
		}
		if (!trim($this->schedule->hours) && !$this->schedule->hour_ranges) {
			$this->schedule->hours = '00:00:00';
		}
		[$date_year, $date_month, $date_day, $date_time]
			= explode('-', $this->date->format('Y-m-d-H:i:s'));
		$hour_ranges = $this->schedule->getExtendedHourRanges();
		// last time exceeded : next day
		if ($date_time > end($hour_ranges)->until) {
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, $date_month, intval($date_day) + 1, $date_year));
			$this->nextDayOfWeek();
			$this->nextTime();
			return true;
		}
		// current or next available range
		$next_date = $this->date;
		$test_time = $date_time;
		/** @var $possible_dates Date_Time[] */
		$possible_dates = [];
		foreach ($hour_ranges as $hour_range) {
			if ($forward) {
				if ($hour_range->frequency) {
					/** @noinspection PhpUnhandledExceptionInspection date must be valid */
					$next_date = (new Date_Time($this->date))->add(
						$hour_range->frequency, substr($hour_range->frequency_unit, 0, -1)
					)->sub(1, Date_Time::SECOND);
					$test_time = $next_date->format('H:i:s');
				}
				else {
					$next_date = $this->date;
					$test_time = $date_time;
				}
			}
			if ($hour_range->from >= $test_time) {
				$hour             = explode(':', $hour_range->from);
				/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
				$possible_dates[] = new Date_Time(mktime(
					$hour[0], $hour[1], $hour[2],
					$next_date->format('m'), $next_date->format('d'), $next_date->format('Y')
				));
				break;
			}
			if ($hour_range->until >= $test_time) {
				$possible_dates[] = $next_date;
			}
		}
		if (!$possible_dates) {
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, $date_month, intval($date_day) + 1, $date_year));
			$this->nextDayOfWeek();
			$this->nextTime();
			return true;
		}
		$this->date = array_shift($possible_dates);
		foreach ($possible_dates as $possible_date) {
			if ($possible_date->isBefore($this->date)) {
				$this->date = $possible_date;
			}
		}
		if ($this->nextDayOfWeek()) {
			$this->nextTime();
		}
		return $forward;
	}

	//-------------------------------------------------------------------------------------- nextYear
	/**
	 * Calculate next allowed year
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return boolean true if date changed, else false
	 */
	protected function nextYear() : bool
	{
		if ($this->date->isMax() || !trim($this->schedule->years)) {
			return false;
		}
		$date_year = $this->date->format('Y');
		$years     = $this->schedule->getYears();
		// last year exceeded
		if ($date_year > end($years)) {
			$this->date = Date_Time::max();
			return true;
		}
		// current or next available year
		if (!in_array($date_year, $years)) {
			foreach ($years as $year) {
				if ($year > $date_year) {
					$date_year = $year;
					break;
				}
			}
			/** @noinspection PhpUnhandledExceptionInspection mktime result is valid */
			$this->date = new Date_Time(mktime(0, 0, 0, 1, 1, $date_year));
			return true;
		}
		return false;
	}

}
