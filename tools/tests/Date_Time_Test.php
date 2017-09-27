<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Interval;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Date_Time tools class unit tests
 */
class Date_Time_Test extends Test
{

	//--------------------------------------------------------------------------------------- testAdd
	public function testAdd()
	{
		$today              = new Date_Time('2016-09-23 11:04:02');
		$tomorrow           = new Date_Time('2016-09-24 11:04:02');
		$one_minute_ago     = new Date_Time('2016-09-24 11:03:02');
		$two_hours_after    = new Date_Time('2016-09-24 13:03:02');
		$three_years_before = new Date_Time('2013-09-24 13:03:02');
		$this->method(__METHOD__);
		$this->assume('1 day',          $today->add(1),                     $tomorrow);
		$this->assume('1 minute ago',   $today->add(-1, Date_Time::MINUTE), $one_minute_ago);
		$this->assume('2 hours after',  $today->add(2,  Date_Time::HOUR),   $two_hours_after);
		$this->assume('3 years before', $today->add(-3, Date_Time::YEAR),   $three_years_before);
	}

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct()
	{
		$date = new Date_Time('2016-11-05T19:46:32.56');
		$this->method(__METHOD__);
		$this->assume(__METHOD__, $date->format('Y-m-d H:i:s'), '2016-11-05 19:46:32');
	}

	//-------------------------------------------------------------------------------------- testDiff
	/**
	 * @see Date_Time::diff()
	 */
	public function testDiff()
	{
		$yesterday = new Date_Time('2016-11-01 16:11:00');
		$tomorrow  = new Date_Time('2016-11-03 15:10:49');

		// Chronological
		$actual = $yesterday->diff($tomorrow);
		$this->assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		$this->assertEquals(0, $actual->invert);

		// Chronological absolute
		$actual = $yesterday->diff($tomorrow, true);
		$this->assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		$this->assertEquals(0, $actual->invert);

		// Reverse
		$actual = $tomorrow->diff($yesterday);
		$this->assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		$this->assertEquals(1, $actual->invert);

		// Reverse absolute
		$actual = $tomorrow->diff($yesterday, true);
		$this->assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		$this->assertEquals(0, $actual->invert);
	}

	//---------------------------------------------------------------------------------- testEarliest
	public function testEarliest()
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		$this->method(__METHOD__);
		$this->assume('one argument',      $earlier->earliest($later),       $earlier);
		$this->assume('two arguments',     $earlier->earliest($later, $now), $earlier);
		$this->assume('reverse arguments', $earlier->earliest($now, $later), $earlier);
		$this->assume('another reverse',   $now->earliest($earlier, $later), $earlier);
		$this->assume('another 2',         $now->earliest($later, $earlier), $earlier);
		$this->assume('another 3',         $later->earliest($earlier, $now), $earlier);
		$this->assume('another 4',         $later->earliest($now, $earlier), $earlier);
	}

	//---------------------------------------------------------------------------------------- testIs
	public function testIs()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date      = new Date_Time($empty_string);
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyIsEmptyString',    $empty_date->is($empty_string),    true);
		$this->assume('emptyIsTodayString',    $empty_date->is($today_string),    false);
		$this->assume('emptyIsTomorrow',       $empty_date->is($tomorrow_string), false);
		$this->assume('emptyIsEmptyDate',      $empty_date->is($empty_date),      true);
		$this->assume('emptyIsTodayDate',      $empty_date->is($today_date),      false);
		$this->assume('emptyIsMinDate',        $empty_date->is($min_date),        true);
		$this->assume('emptyIsMaxDate',        $empty_date->is($max_date),        false);
		$this->assume('todayIsEmptyString',    $today_date->is($empty_string),    false);
		$this->assume('todayIsTodayString',    $today_date->is($today_string),    true);
		$this->assume('todayIsTomorrowString', $today_date->is($tomorrow_string), false);
		$this->assume('todayIsEmptyDate',      $today_date->is($empty_date),      false);
		$this->assume('todayIsMinDate',        $today_date->is($min_date),        false);
		$this->assume('todayIsTodayDate',      $today_date->is($today_date),      true);
		$this->assume('todayIsMaxDate',        $today_date->is($max_date),        false);
		$this->assume('minIsEmptyString',      $min_date->is($empty_string),      true);
		$this->assume('minIsTodayString',      $min_date->is($today_string),      false);
		$this->assume('minIsTomorrowString',   $min_date->is($tomorrow_string),   false);
		$this->assume('minIsEmptyDate',        $min_date->is($empty_date),        true);
		$this->assume('minIsTodayDate',        $min_date->is($today_date),        false);
		$this->assume('minIsMinDate',          $min_date->is($min_date),          true);
		$this->assume('minIsMaxDate',          $min_date->is($max_date),          false);
		$this->assume('maxIsEmptyString',      $max_date->is($empty_string),      false);
		$this->assume('maxIsTodayString',      $max_date->is($today_string),      false);
		$this->assume('maxIsTomorrowString',   $max_date->is($tomorrow_string),   false);
		$this->assume('maxIsEmptyDate',        $max_date->is($empty_date),        false);
		$this->assume('maxIsTodayDate',        $max_date->is($today_date),        false);
		$this->assume('minIsMinDate',          $max_date->is($min_date),          false);
		$this->assume('maxIsMaxDate',          $max_date->is($max_date),          true);
	}

	//----------------------------------------------------------------------------------- testIsAfter
	public function testIsAfter()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date      = new Date_Time($empty_string);
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyAfterEmptyString',    $empty_date->isAfter($empty_string),    false);
		$this->assume('emptyAfterTodayString',    $empty_date->isAfter($today_string),    false);
		$this->assume('emptyAfterTomorrow',       $empty_date->isAfter($tomorrow_string), false);
		$this->assume('emptyAfterEmptyDate',      $empty_date->isAfter($empty_date),      false);
		$this->assume('emptyAfterTodayDate',      $empty_date->isAfter($today_date),      false);
		$this->assume('emptyAfterMinDate',        $empty_date->isAfter($min_date),        false);
		$this->assume('emptyAfterMaxDate',        $empty_date->isAfter($max_date),        false);
		$this->assume('todayAfterEmptyString',    $today_date->isAfter($empty_string),    true);
		$this->assume('todayAfterTodayString',    $today_date->isAfter($today_string),    false);
		$this->assume('todayAfterTomorrowString', $today_date->isAfter($tomorrow_string), false);
		$this->assume('todayAfterEmptyDate',      $today_date->isAfter($empty_date),      true);
		$this->assume('todayAfterMinDate',        $today_date->isAfter($min_date),        true);
		$this->assume('todayAfterTodayDate',      $today_date->isAfter($today_date),      false);
		$this->assume('todayAfterMaxDate',        $today_date->isAfter($max_date),        false);
		$this->assume('minAfterEmptyString',      $min_date->isAfter($empty_string),      false);
		$this->assume('minAfterTodayString',      $min_date->isAfter($today_string),      false);
		$this->assume('minAfterTomorrowString',   $min_date->isAfter($tomorrow_string),   false);
		$this->assume('minAfterEmptyDate',        $min_date->isAfter($empty_date),        false);
		$this->assume('minAfterTodayDate',        $min_date->isAfter($today_date),        false);
		$this->assume('minAfterMinDate',          $min_date->isAfter($min_date),          false);
		$this->assume('minAfterMaxDate',          $min_date->isAfter($max_date),          false);
		$this->assume('maxAfterEmptyString',      $max_date->isAfter($empty_string),      true);
		$this->assume('maxAfterTodayString',      $max_date->isAfter($today_string),      true);
		$this->assume('maxAfterTomorrowString',   $max_date->isAfter($tomorrow_string),   true);
		$this->assume('maxAfterEmptyDate',        $max_date->isAfter($empty_date),        true);
		$this->assume('maxAfterTodayDate',        $max_date->isAfter($today_date),        true);
		$this->assume('minAfterMinDate',          $max_date->isAfter($min_date),          true);
		$this->assume('maxAfterMaxDate',          $max_date->isAfter($max_date),          false);
	}

	//---------------------------------------------------------------------------- testIsAfterOrEqual
	public function testIsAfterOrEqual()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date      = new Date_Time($empty_string);
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyAfterEmptyString',    $empty_date->isAfterOrEqual($empty_string),    true);
		$this->assume('emptyAfterTodayString',    $empty_date->isAfterOrEqual($today_string),    false);
		$this->assume('emptyAfterTomorrow',       $empty_date->isAfterOrEqual($tomorrow_string), false);
		$this->assume('emptyAfterEmptyDate',      $empty_date->isAfterOrEqual($empty_date),      true);
		$this->assume('emptyAfterTodayDate',      $empty_date->isAfterOrEqual($today_date),      false);
		$this->assume('emptyAfterMinDate',        $empty_date->isAfterOrEqual($min_date),        true);
		$this->assume('emptyAfterMaxDate',        $empty_date->isAfterOrEqual($max_date),        false);
		$this->assume('todayAfterEmptyString',    $today_date->isAfterOrEqual($empty_string),    true);
		$this->assume('todayAfterTodayString',    $today_date->isAfterOrEqual($today_string),    true);
		$this->assume('todayAfterTomorrowString', $today_date->isAfterOrEqual($tomorrow_string), false);
		$this->assume('todayAfterEmptyDate',      $today_date->isAfterOrEqual($empty_date),      true);
		$this->assume('todayAfterMinDate',        $today_date->isAfterOrEqual($min_date),        true);
		$this->assume('todayAfterTodayDate',      $today_date->isAfterOrEqual($today_date),      true);
		$this->assume('todayAfterMaxDate',        $today_date->isAfterOrEqual($max_date),        false);
		$this->assume('minAfterEmptyString',      $min_date->isAfterOrEqual($empty_string),      true);
		$this->assume('minAfterTodayString',      $min_date->isAfterOrEqual($today_string),      false);
		$this->assume('minAfterTomorrowString',   $min_date->isAfterOrEqual($tomorrow_string),   false);
		$this->assume('minAfterEmptyDate',        $min_date->isAfterOrEqual($empty_date),        true);
		$this->assume('minAfterTodayDate',        $min_date->isAfterOrEqual($today_date),        false);
		$this->assume('minAfterMinDate',          $min_date->isAfterOrEqual($min_date),          true);
		$this->assume('minAfterMaxDate',          $min_date->isAfterOrEqual($max_date),          false);
		$this->assume('maxAfterEmptyString',      $max_date->isAfterOrEqual($empty_string),      true);
		$this->assume('maxAfterTodayString',      $max_date->isAfterOrEqual($today_string),      true);
		$this->assume('maxAfterTomorrowString',   $max_date->isAfterOrEqual($tomorrow_string),   true);
		$this->assume('maxAfterEmptyDate',        $max_date->isAfterOrEqual($empty_date),        true);
		$this->assume('maxAfterTodayDate',        $max_date->isAfterOrEqual($today_date),        true);
		$this->assume('minAfterMinDate',          $max_date->isAfterOrEqual($min_date),          true);
		$this->assume('maxAfterMaxDate',          $max_date->isAfterOrEqual($max_date),          true);
	}

	//---------------------------------------------------------------------------------- testIsBefore
	public function testIsBefore()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date      = new Date_Time($empty_string);
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyBeforeEmptyString',    $empty_date->isBefore($empty_string),    false);
		$this->assume('emptyBeforeTodayString',    $empty_date->isBefore($today_string),    true);
		$this->assume('emptyBeforeTomorrow',       $empty_date->isBefore($tomorrow_string), true);
		$this->assume('emptyBeforeEmptyDate',      $empty_date->isBefore($empty_date),      false);
		$this->assume('emptyBeforeTodayDate',      $empty_date->isBefore($today_date),      true);
		$this->assume('emptyBeforeMinDate',        $empty_date->isBefore($min_date),        false);
		$this->assume('emptyBeforeMaxDate',        $empty_date->isBefore($max_date),        true);
		$this->assume('todayBeforeEmptyString',    $today_date->isBefore($empty_string),    false);
		$this->assume('todayBeforeTodayString',    $today_date->isBefore($today_string),    false);
		$this->assume('todayBeforeTomorrowString', $today_date->isBefore($tomorrow_string), true);
		$this->assume('todayBeforeEmptyDate',      $today_date->isBefore($empty_date),      false);
		$this->assume('todayBeforeMinDate',        $today_date->isBefore($min_date),        false);
		$this->assume('todayBeforeTodayDate',      $today_date->isBefore($today_date),      false);
		$this->assume('todayBeforeMaxDate',        $today_date->isBefore($max_date),        true);
		$this->assume('minBeforeEmptyString',      $min_date->isBefore($empty_string),      false);
		$this->assume('minBeforeTodayString',      $min_date->isBefore($today_string),      true);
		$this->assume('minBeforeTomorrowString',   $min_date->isBefore($tomorrow_string),   true);
		$this->assume('minBeforeEmptyDate',        $min_date->isBefore($empty_date),        false);
		$this->assume('minBeforeTodayDate',        $min_date->isBefore($today_date),        true);
		$this->assume('minBeforeMinDate',          $min_date->isBefore($min_date),          false);
		$this->assume('minBeforeMaxDate',          $min_date->isBefore($max_date),          true);
		$this->assume('maxBeforeEmptyString',      $max_date->isBefore($empty_string),      false);
		$this->assume('maxBeforeTodayString',      $max_date->isBefore($today_string),      false);
		$this->assume('maxBeforeTomorrowString',   $max_date->isBefore($tomorrow_string),   false);
		$this->assume('maxBeforeEmptyDate',        $max_date->isBefore($empty_date),        false);
		$this->assume('maxBeforeTodayDate',        $max_date->isBefore($today_date),        false);
		$this->assume('minBeforeMinDate',          $max_date->isBefore($min_date),          false);
		$this->assume('maxBeforeMaxDate',          $max_date->isBefore($max_date),          false);
		$this->method('-');
	}

	//--------------------------------------------------------------------------- testIsBeforeOrEqual
	public function testIsBeforeOrEqual()
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		$empty_date      = new Date_Time($empty_string);
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('emptyBeforeEmptyString',    $empty_date->isBeforeOrEqual($empty_string),    true);
		$this->assume('emptyBeforeTodayString',    $empty_date->isBeforeOrEqual($today_string),    true);
		$this->assume('emptyBeforeTomorrow',       $empty_date->isBeforeOrEqual($tomorrow_string), true);
		$this->assume('emptyBeforeEmptyDate',      $empty_date->isBeforeOrEqual($empty_date),      true);
		$this->assume('emptyBeforeTodayDate',      $empty_date->isBeforeOrEqual($today_date),      true);
		$this->assume('emptyBeforeMinDate',        $empty_date->isBeforeOrEqual($min_date),        true);
		$this->assume('emptyBeforeMaxDate',        $empty_date->isBeforeOrEqual($max_date),        true);
		$this->assume('todayBeforeEmptyString',    $today_date->isBeforeOrEqual($empty_string),    false);
		$this->assume('todayBeforeTodayString',    $today_date->isBeforeOrEqual($today_string),    true);
		$this->assume('todayBeforeTomorrowString', $today_date->isBeforeOrEqual($tomorrow_string), true);
		$this->assume('todayBeforeEmptyDate',      $today_date->isBeforeOrEqual($empty_date),      false);
		$this->assume('todayBeforeMinDate',        $today_date->isBeforeOrEqual($min_date),        false);
		$this->assume('todayBeforeTodayDate',      $today_date->isBeforeOrEqual($today_date),      true);
		$this->assume('todayBeforeMaxDate',        $today_date->isBeforeOrEqual($max_date),        true);
		$this->assume('minBeforeEmptyString',      $min_date->isBeforeOrEqual($empty_string),      true);
		$this->assume('minBeforeTodayString',      $min_date->isBeforeOrEqual($today_string),      true);
		$this->assume('minBeforeTomorrowString',   $min_date->isBeforeOrEqual($tomorrow_string),   true);
		$this->assume('minBeforeEmptyDate',        $min_date->isBeforeOrEqual($empty_date),        true);
		$this->assume('minBeforeTodayDate',        $min_date->isBeforeOrEqual($today_date),        true);
		$this->assume('minBeforeMinDate',          $min_date->isBeforeOrEqual($min_date),          true);
		$this->assume('minBeforeMaxDate',          $min_date->isBeforeOrEqual($max_date),          true);
		$this->assume('maxBeforeEmptyString',      $max_date->isBeforeOrEqual($empty_string),      false);
		$this->assume('maxBeforeTodayString',      $max_date->isBeforeOrEqual($today_string),      false);
		$this->assume('maxBeforeTomorrowString',   $max_date->isBeforeOrEqual($tomorrow_string),   false);
		$this->assume('maxBeforeEmptyDate',        $max_date->isBeforeOrEqual($empty_date),        false);
		$this->assume('maxBeforeTodayDate',        $max_date->isBeforeOrEqual($today_date),        false);
		$this->assume('minBeforeMinDate',          $max_date->isBeforeOrEqual($min_date),          false);
		$this->assume('maxBeforeMaxDate',          $max_date->isBeforeOrEqual($max_date),          true);
	}

	//----------------------------------------------------------------------------------- testIsEmpty
	public function testIsEmpty()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('empty', $empty_date->isEmpty(), true);
		$this->assume('today', $today_date->isEmpty(), false);
		$this->assume('min',   $min_date->isEmpty(),   true);
		$this->assume('max',   $max_date->isEmpty(),   true);
	}

	//------------------------------------------------------------------------------------- testIsMax
	public function testIsMax()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('empty', $empty_date->isMax(), false);
		$this->assume('today', $today_date->isMax(), false);
		$this->assume('min',   $min_date->isMax(),   false);
		$this->assume('max',   $max_date->isMax(),   true);
	}

	//------------------------------------------------------------------------------------- testIsMin
	public function testIsMin()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->method(__METHOD__);
		$this->assume('empty', $empty_date->isMin(), true);
		$this->assume('today', $today_date->isMin(), false);
		$this->assume('min',   $min_date->isMin(),   true);
		$this->assume('max',   $max_date->isMin(),   false);
		$this->method('-');
	}

	//------------------------------------------------------------------------------------ testLatest
	public function testLatest()
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		$this->method(__METHOD__);
		$this->assume('one argument',      $earlier->latest($later),       $later);
		$this->assume('two arguments',     $earlier->latest($later, $now), $later);
		$this->assume('reverse arguments', $earlier->latest($now, $later), $later);
		$this->assume('another reverse',   $now->latest($earlier, $later), $later);
		$this->assume('another 2',         $now->latest($later, $earlier), $later);
		$this->assume('another 3',         $later->latest($earlier, $now), $later);
		$this->assume('another 4',         $later->latest($now, $earlier), $later);
	}

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth()
	{
		$month = (new Date_Time('2016-06-04 12:35:00'))->month()->format('Y-m-d H:i:s');
		$this->assume(__METHOD__, $month, '2016-06-01 00:00:00');
	}

	//--------------------------------------------------------------------------------- testYesterday
	/**
	 * Test method Date_Time::yesterday().
	 */
	public function testYesterday()
	{
		$today     = Date_Time::today();
		$yesterday = Date_Time::yesterday();
		$actual    = $today->diff($yesterday);

		$expected = '-1 day 0 hour 0 minute 0 second';

		$this->assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
	}

}
