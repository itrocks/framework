<?php
namespace ITRocks\Framework\Trigger\Schedule\Next_Calculation;

use ITRocks\Framework\Tests;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Schedule;
use ITRocks\Framework\Trigger\Schedule\Hour_Range;
use ITRocks\Framework\Trigger\Schedule\Next_Calculation;

/**
 * Schedule next calculation unit tests
 */
class Test extends Tests\Test
{

	//---------------------------------------------------------------------------------- executeTests
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $schedule Schedule
	 * @param $values   string[] key is the source value, expected is the next one
	 */
	protected function executeTests(Schedule $schedule, array $values) : void
	{
		$calculation = new Next_Calculation();
		foreach ($values as $source => $expected) {
			/** @noinspection PhpUnhandledExceptionInspection $source must be valid */
			static::assertEquals(
				$expected,
				$calculation->next($schedule, new Date_Time($source))->format('Y-m-d H:i:s'),
				'For source ' . $source
			);
		}
	}

	//-------------------------------------------------------------------------------- testDaysOfWeek
	public function testDaysOfWeek() : void
	{
		$schedule = new Schedule();

		$schedule->days_of_week = ['saturday', 'sunday'];

		$hour_range            = new Hour_Range();
		$hour_range->frequency = 20;
		$hour_range->from      = '08:00';
		$hour_range->until     = '17:00';
		$schedule->hour_ranges = [$hour_range];

		$this->executeTests($schedule, [
			'2018-09-09 12:16:13' => '2018-09-09 12:36:13',
			'2018-09-10 12:16:13' => '2018-09-15 08:00:00',
			'2018-09-11 12:16:13' => '2018-09-15 08:00:00',
			'2018-09-12 12:16:13' => '2018-09-15 08:00:00',
			'2018-09-13 12:16:13' => '2018-09-15 08:00:00',
			'2018-09-14 12:16:13' => '2018-09-15 08:00:00',
			'2018-09-15 12:16:13' => '2018-09-15 12:36:13',
		]);
	}

	//------------------------------------------------------------------------------- testFiveMinutes
	public function testFiveMinutes() : void
	{
		$schedule = new Schedule();

		$hour_range            = new Hour_Range();
		$hour_range->frequency = 5;
		$schedule->hour_ranges = [$hour_range];

		$this->executeTests($schedule, [
			'2018-09-15 12:16:13' => '2018-09-15 12:21:13',
			'2018-09-15 23:59:11' => '2018-09-16 00:04:11',
		]);
	}

	//-------------------------------------------------------------------------------- testHourRanges
	public function testHourRanges() : void
	{
		$schedule = new Schedule();

		$hour_range              = new Hour_Range();
		$hour_range->from        = '12';
		$hour_range->until       = '13:18';
		$hour_range->frequency   = 15;
		$schedule->hour_ranges[] = $hour_range;

		$hour_range = new Hour_Range();
		$hour_range->from           = '14:00:15';
		$hour_range->until          = '16';
		$hour_range->frequency      = 15;
		$hour_range->frequency_unit = 'seconds';
		$schedule->hour_ranges[]    = $hour_range;

		$hour_range                 = new Hour_Range();
		$hour_range->from           = '14:01:00';
		$hour_range->until          = '14:01:15';
		$hour_range->frequency      = 5;
		$hour_range->frequency_unit = 'seconds';
		$schedule->hour_ranges[]    = $hour_range;

		$hour_range = new Hour_Range();
		$hour_range->from      = '18:21:15';
		$hour_range->until     = '18:22:13';
		$hour_range->frequency = 15;
		$schedule->hour_ranges[] = $hour_range;

		$schedule->hours = '13:01';

		$this->executeTests($schedule, [
			'2018-09-15 08:00:00' => '2018-09-15 12:00:00',
			'2018-09-15 12:00:00' => '2018-09-15 12:15:00',
			'2018-09-15 12:15:00' => '2018-09-15 12:30:00',
			'2018-09-15 12:30:00' => '2018-09-15 12:45:00',
			'2018-09-15 12:45:00' => '2018-09-15 13:00:00',
			'2018-09-15 13:00:00' => '2018-09-15 13:01:00',
			'2018-09-15 13:01:00' => '2018-09-15 13:16:00',
			'2018-09-15 13:01:10' => '2018-09-15 13:16:10',
			'2018-09-15 13:16:00' => '2018-09-15 14:00:15',
			'2018-09-15 14:00:15' => '2018-09-15 14:00:30',
			'2018-09-15 14:00:30' => '2018-09-15 14:00:45',
			'2018-09-15 14:00:45' => '2018-09-15 14:01:00',
			'2018-09-15 14:01:00' => '2018-09-15 14:01:05',
			'2018-09-15 14:01:05' => '2018-09-15 14:01:10',
			'2018-09-15 14:01:10' => '2018-09-15 14:01:15',
			'2018-09-15 14:01:15' => '2018-09-15 14:01:30',
			'2018-09-15 16:59:45' => '2018-09-15 18:21:15',
			'2018-09-15 18:21:15' => '2018-09-16 12:00:00',
			'2018-09-15 18:23:00' => '2018-09-16 12:00:00',
		]);
	}

	//------------------------------------------------------------------------------------ testMixed1
	/**
	 * Mixed configuration test
	 */
	public function testMixed1() : void
	{
		$schedule = new Schedule();
		$schedule->days_of_week = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday'];

		$hour_range              = new Hour_Range();
		$hour_range->frequency   = 15;
		$schedule->hour_ranges[] = $hour_range;

		$this->executeTests($schedule, [
			'2018-09-15 08:00:00' => '2018-09-17 00:00:00',
			'2018-09-17 00:00:00' => '2018-09-17 00:15:00',
			'2018-09-17 23:59:59' => '2018-09-18 00:14:59',
			'2018-09-18 23:59:59' => '2018-09-19 00:14:59',
			'2018-09-19 23:59:59' => '2018-09-20 00:14:59',
			'2018-09-20 23:59:59' => '2018-09-21 00:14:59',
			'2018-09-21 23:59:59' => '2018-09-24 00:00:00',
			'2018-09-22 23:59:59' => '2018-09-24 00:00:00',
			'2018-09-23 23:59:59' => '2018-09-24 00:14:59',
		]);
	}

	//------------------------------------------------------------------------------------- testYears
	/**
	 * Test years
	 */
	public function testYears() : void
	{
		$schedule                = new Schedule();
		$schedule->days_of_month = 1;
		$schedule->months        = 1;
		$schedule->years         = '2018,2019';

		$this->executeTests($schedule, [
			'2018-09-15 08:00:00' => '2019-01-01 00:00:00'
		]);
	}

}
