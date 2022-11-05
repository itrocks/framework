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

	//----------------------------------------------------------------------------------- providerAdd
	/**
	 * @return array
	 * @see testAdd
	 */
	public function providerAdd() : array
	{
		return [
			'1 day'          => [new Date_Time('2016-09-24T11:04:02'), [ 1                   ]],
			'1 minute ago'   => [new Date_Time('2016-09-23T11:03:02'), [-1, Date_Time::MINUTE]],
			'2 hours after'  => [new Date_Time('2016-09-23T13:04:02'), [ 2, Date_Time::HOUR  ]],
			'3 years before' => [new Date_Time('2013-09-23T11:04:02'), [-3, Date_Time::YEAR  ]],
		];
	}

	//--------------------------------------------------------------------------------------- testAdd
	/**
	 * @dataProvider providerAdd
	 * @param $expected Date_Time
	 * @param $args int[]|string[]
	 */
	public function testAdd(Date_Time $expected, array $args) : void
	{
		$base = new Date_Time('2016-09-23T11:04:02');
		static::assertEquals($expected, $base->add(...$args));
	}

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct() : void
	{
		$date = new Date_Time('2016-11-05T19:46:32.56');
		static::assertEquals('2016-11-05 19:46:32', $date->format('Y-m-d H:i:s'));
	}

	//-------------------------------------------------------------------------------------- testDiff
	public function testDiff() : void
	{
		$yesterday = new Date_Time('2016-11-01 16:11:00');
		$tomorrow  = new Date_Time('2016-11-03 15:10:49');

		// Chronological
		$actual = $yesterday->diff($tomorrow);
		static::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		static::assertEquals(0, $actual->invert);

		// Chronological absolute
		$actual = $yesterday->diff($tomorrow, true);
		static::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		static::assertEquals(0, $actual->invert);

		// Reverse
		$actual = $tomorrow->diff($yesterday);
		static::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		static::assertEquals(1, $actual->invert);

		// Reverse absolute
		$actual = $tomorrow->diff($yesterday, true);
		static::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		static::assertEquals(0, $actual->invert);
	}

	//---------------------------------------------------------------------------------- testEarliest
	public function testEarliest() : void
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		 static::assertEquals($earlier, $earlier->earliest($later),       'one argument');
		 static::assertEquals($earlier, $earlier->earliest($later, $now), 'two arguments');
		 static::assertEquals($earlier, $earlier->earliest($now, $later), 'reverse arguments');
		 static::assertEquals($earlier, $now->earliest($earlier, $later), 'another reverse');
		 static::assertEquals($earlier, $now->earliest($later, $earlier), 'another 2');
		 static::assertEquals($earlier, $later->earliest($earlier, $now), 'another 3');
		 static::assertEquals($earlier, $later->earliest($now, $earlier), 'another 4');
	}

	//---------------------------------------------------------------------------------------- testIs
	public function testIs() : void
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$empty_date      = new Date_Time($empty_string);
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		static::assertTrue ($empty_date->is($empty_string),    'emptyIsEmptyString');
		static::assertFalse($empty_date->is($today_string),    'emptyIsTodayString');
		static::assertFalse($empty_date->is($tomorrow_string), 'emptyIsTomorrow');
		static::assertTrue ($empty_date->is($empty_date),      'emptyIsEmptyDate');
		static::assertFalse($empty_date->is($today_date),      'emptyIsTodayDate');
		static::assertTrue ($empty_date->is($min_date),        'emptyIsMinDate');
		static::assertFalse($empty_date->is($max_date),        'emptyIsMaxDate');
		static::assertFalse($today_date->is($empty_string),    'todayIsEmptyString');
		static::assertTrue ($today_date->is($today_string),    'todayIsTodayString');
		static::assertFalse($today_date->is($tomorrow_string), 'todayIsTomorrowString');
		static::assertFalse($today_date->is($empty_date),      'todayIsEmptyDate');
		static::assertFalse($today_date->is($min_date),        'todayIsMinDate');
		static::assertTrue ($today_date->is($today_date),      'todayIsTodayDate');
		static::assertFalse($today_date->is($max_date),        'todayIsMaxDate');
		static::assertTrue (  $min_date->is($empty_string),    'minIsEmptyString');
		static::assertFalse(  $min_date->is($today_string),    'minIsTodayString');
		static::assertFalse(  $min_date->is($tomorrow_string), 'minIsTomorrowString');
		static::assertTrue (  $min_date->is($empty_date),      'minIsEmptyDate');
		static::assertFalse(  $min_date->is($today_date),      'minIsTodayDate');
		static::assertTrue (  $min_date->is($min_date),        'minIsMinDate');
		static::assertFalse(  $min_date->is($max_date),        'minIsMaxDate');
		static::assertFalse(  $max_date->is($empty_string),    'maxIsEmptyString');
		static::assertFalse(  $max_date->is($today_string),    'maxIsTodayString');
		static::assertFalse(  $max_date->is($tomorrow_string), 'maxIsTomorrowString');
		static::assertFalse(  $max_date->is($empty_date),      'maxIsEmptyDate');
		static::assertFalse(  $max_date->is($today_date),      'maxIsTodayDate');
		static::assertFalse(  $max_date->is($min_date),        'minIsMinDate');
		static::assertTrue (  $max_date->is($max_date),        'maxIsMaxDate');
	}

	//----------------------------------------------------------------------------------- testIsAfter
	public function testIsAfter() : void
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$empty_date      = new Date_Time($empty_string);
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		static::assertFalse($empty_date->isAfter($empty_string),    'emptyAfterEmptyString');
		static::assertFalse($empty_date->isAfter($today_string),    'emptyAfterTodayString');
		static::assertFalse($empty_date->isAfter($tomorrow_string), 'emptyAfterTomorrow');
		static::assertFalse($empty_date->isAfter($empty_date),      'emptyAfterEmptyDate');
		static::assertFalse($empty_date->isAfter($today_date),      'emptyAfterTodayDate');
		static::assertFalse($empty_date->isAfter($min_date),        'emptyAfterMinDate');
		static::assertFalse($empty_date->isAfter($max_date),        'emptyAfterMaxDate');
		static::assertTrue ($today_date->isAfter($empty_string),    'todayAfterEmptyString');
		static::assertFalse($today_date->isAfter($today_string),    'todayAfterTodayString');
		static::assertFalse($today_date->isAfter($tomorrow_string), 'todayAfterTomorrowString');
		static::assertTrue ($today_date->isAfter($empty_date),      'todayAfterEmptyDate');
		static::assertTrue ($today_date->isAfter($min_date),        'todayAfterMinDate');
		static::assertFalse($today_date->isAfter($today_date),      'todayAfterTodayDate');
		static::assertFalse($today_date->isAfter($max_date),        'todayAfterMaxDate');
		static::assertFalse(  $min_date->isAfter($empty_string),    'minAfterEmptyString');
		static::assertFalse(  $min_date->isAfter($today_string),    'minAfterTodayString');
		static::assertFalse(  $min_date->isAfter($tomorrow_string), 'minAfterTomorrowString');
		static::assertFalse(  $min_date->isAfter($empty_date),      'minAfterEmptyDate');
		static::assertFalse(  $min_date->isAfter($today_date),      'minAfterTodayDate');
		static::assertFalse(  $min_date->isAfter($min_date),        'minAfterMinDate');
		static::assertFalse(  $min_date->isAfter($max_date),        'minAfterMaxDate');
		static::assertTrue (  $max_date->isAfter($empty_string),    'maxAfterEmptyString');
		static::assertTrue (  $max_date->isAfter($today_string),    'maxAfterTodayString');
		static::assertTrue (  $max_date->isAfter($tomorrow_string), 'maxAfterTomorrowString');
		static::assertTrue (  $max_date->isAfter($empty_date),      'maxAfterEmptyDate');
		static::assertTrue (  $max_date->isAfter($today_date),      'maxAfterTodayDate');
		static::assertTrue (  $max_date->isAfter($min_date),        'minAfterMinDate');
		static::assertFalse(  $max_date->isAfter($max_date),        'maxAfterMaxDate');
	}

	//---------------------------------------------------------------------------- testIsAfterOrEqual
	public function testIsAfterOrEqual() : void
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$empty_date      = new Date_Time($empty_string);
		/** @noinspection PhpUnhandledExceptionInspection valid date-time */
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		static::assertTrue ($empty_date->isAfterOrEqual($empty_string),    'emptyAfterEmptyString');
		static::assertFalse($empty_date->isAfterOrEqual($today_string),    'emptyAfterTodayString');
		static::assertFalse($empty_date->isAfterOrEqual($tomorrow_string), 'emptyAfterTomorrow');
		static::assertTrue ($empty_date->isAfterOrEqual($empty_date),      'emptyAfterEmptyDate');
		static::assertFalse($empty_date->isAfterOrEqual($today_date),      'emptyAfterTodayDate');
		static::assertTrue ($empty_date->isAfterOrEqual($min_date),        'emptyAfterMinDate');
		static::assertFalse($empty_date->isAfterOrEqual($max_date),        'emptyAfterMaxDate');
		static::assertTrue ($today_date->isAfterOrEqual($empty_string),    'todayAfterEmptyString');
		static::assertTrue ($today_date->isAfterOrEqual($today_string),    'todayAfterTodayString');
		static::assertFalse($today_date->isAfterOrEqual($tomorrow_string), 'todayAfterTomorrowString');
		static::assertTrue ($today_date->isAfterOrEqual($empty_date),      'todayAfterEmptyDate');
		static::assertTrue ($today_date->isAfterOrEqual($min_date),        'todayAfterMinDate');
		static::assertTrue ($today_date->isAfterOrEqual($today_date),      'todayAfterTodayDate');
		static::assertFalse($today_date->isAfterOrEqual($max_date),        'todayAfterMaxDate');
		static::assertTrue (  $min_date->isAfterOrEqual($empty_string),    'minAfterEmptyString');
		static::assertFalse(  $min_date->isAfterOrEqual($today_string),    'minAfterTodayString');
		static::assertFalse(  $min_date->isAfterOrEqual($tomorrow_string), 'minAfterTomorrowString');
		static::assertTrue (  $min_date->isAfterOrEqual($empty_date),      'minAfterEmptyDate');
		static::assertFalse(  $min_date->isAfterOrEqual($today_date),      'minAfterTodayDate');
		static::assertTrue (  $min_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		static::assertFalse(  $min_date->isAfterOrEqual($max_date),        'minAfterMaxDate');
		static::assertTrue (  $max_date->isAfterOrEqual($empty_string),    'maxAfterEmptyString');
		static::assertTrue (  $max_date->isAfterOrEqual($today_string),    'maxAfterTodayString');
		static::assertTrue (  $max_date->isAfterOrEqual($tomorrow_string), 'maxAfterTomorrowString');
		static::assertTrue (  $max_date->isAfterOrEqual($empty_date),      'maxAfterEmptyDate');
		static::assertTrue (  $max_date->isAfterOrEqual($today_date),      'maxAfterTodayDate');
		static::assertTrue (  $max_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		static::assertTrue (  $max_date->isAfterOrEqual($max_date),        'maxAfterMaxDate');
	}

	//---------------------------------------------------------------------------------- testIsBefore
	public function testIsBefore() : void
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$empty_date      = new Date_Time($empty_string);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		static::assertFalse($empty_date->isBefore($empty_string),    'emptyBeforeEmptyString');
		static::assertTrue ($empty_date->isBefore($today_string),    'emptyBeforeTodayString');
		static::assertTrue ($empty_date->isBefore($tomorrow_string), 'emptyBeforeTomorrow');
		static::assertFalse($empty_date->isBefore($empty_date),      'emptyBeforeEmptyDate');
		static::assertTrue ($empty_date->isBefore($today_date),      'emptyBeforeTodayDate');
		static::assertFalse($empty_date->isBefore($min_date),        'emptyBeforeMinDate');
		static::assertTrue ($empty_date->isBefore($max_date),        'emptyBeforeMaxDate');
		static::assertFalse($today_date->isBefore($empty_string),    'todayBeforeEmptyString');
		static::assertFalse($today_date->isBefore($today_string),    'todayBeforeTodayString');
		static::assertTrue ($today_date->isBefore($tomorrow_string), 'todayBeforeTomorrowString');
		static::assertFalse($today_date->isBefore($empty_date),      'todayBeforeEmptyDate');
		static::assertFalse($today_date->isBefore($min_date),        'todayBeforeMinDate');
		static::assertFalse($today_date->isBefore($today_date),      'todayBeforeTodayDate');
		static::assertTrue ($today_date->isBefore($max_date),        'todayBeforeMaxDate');
		static::assertFalse(  $min_date->isBefore($empty_string),    'minBeforeEmptyString');
		static::assertTrue (  $min_date->isBefore($today_string),    'minBeforeTodayString');
		static::assertTrue (  $min_date->isBefore($tomorrow_string), 'minBeforeTomorrowString');
		static::assertFalse(  $min_date->isBefore($empty_date),      'minBeforeEmptyDate');
		static::assertTrue (  $min_date->isBefore($today_date),      'minBeforeTodayDate');
		static::assertFalse(  $min_date->isBefore($min_date),        'minBeforeMinDate');
		static::assertTrue (  $min_date->isBefore($max_date),        'minBeforeMaxDate');
		static::assertFalse(  $max_date->isBefore($empty_string),    'maxBeforeEmptyString');
		static::assertFalse(  $max_date->isBefore($today_string),    'maxBeforeTodayString');
		static::assertFalse(  $max_date->isBefore($tomorrow_string), 'maxBeforeTomorrowString');
		static::assertFalse(  $max_date->isBefore($empty_date),      'maxBeforeEmptyDate');
		static::assertFalse(  $max_date->isBefore($today_date),      'maxBeforeTodayDate');
		static::assertFalse(  $max_date->isBefore($min_date),        'minBeforeMinDate');
		static::assertFalse(  $max_date->isBefore($max_date),        'maxBeforeMaxDate');
	}

	//--------------------------------------------------------------------------- testIsBeforeOrEqual
	public function testIsBeforeOrEqual() : void
	{
		$empty_string    = '0000-00-00 00:00:00';
		$today_string    = '2016-07-13 09:47:05';
		$tomorrow_string = '2016-07-14 00:00:00';
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$empty_date      = new Date_Time($empty_string);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$today_date      = new Date_Time($today_string);
		$min_date        = Date_Time::min();
		$max_date        = Date_Time::max();
		static::assertTrue ($empty_date->isBeforeOrEqual($empty_string),    'emptyBeforeEmptyString');
		static::assertTrue ($empty_date->isBeforeOrEqual($today_string),    'emptyBeforeTodayString');
		static::assertTrue ($empty_date->isBeforeOrEqual($tomorrow_string), 'emptyBeforeTomorrow');
		static::assertTrue ($empty_date->isBeforeOrEqual($empty_date),      'emptyBeforeEmptyDate');
		static::assertTrue ($empty_date->isBeforeOrEqual($today_date),      'emptyBeforeTodayDate');
		static::assertTrue ($empty_date->isBeforeOrEqual($min_date),        'emptyBeforeMinDate');
		static::assertTrue ($empty_date->isBeforeOrEqual($max_date),        'emptyBeforeMaxDate');
		static::assertFalse($today_date->isBeforeOrEqual($empty_string),    'todayBeforeEmptyString');
		static::assertTrue ($today_date->isBeforeOrEqual($today_string),    'todayBeforeTodayString');
		static::assertTrue ($today_date->isBeforeOrEqual($tomorrow_string), 'todayBeforeTomorrowString');
		static::assertFalse($today_date->isBeforeOrEqual($empty_date),      'todayBeforeEmptyDate');
		static::assertFalse($today_date->isBeforeOrEqual($min_date),        'todayBeforeMinDate');
		static::assertTrue ($today_date->isBeforeOrEqual($today_date),      'todayBeforeTodayDate');
		static::assertTrue ($today_date->isBeforeOrEqual($max_date),        'todayBeforeMaxDate');
		static::assertTrue (  $min_date->isBeforeOrEqual($empty_string),    'minBeforeEmptyString');
		static::assertTrue (  $min_date->isBeforeOrEqual($today_string),    'minBeforeTodayString');
		static::assertTrue (  $min_date->isBeforeOrEqual($tomorrow_string), 'minBeforeTomorrowString');
		static::assertTrue (  $min_date->isBeforeOrEqual($empty_date),      'minBeforeEmptyDate');
		static::assertTrue (  $min_date->isBeforeOrEqual($today_date),      'minBeforeTodayDate');
		static::assertTrue (  $min_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		static::assertTrue (  $min_date->isBeforeOrEqual($max_date),        'minBeforeMaxDate');
		static::assertFalse(  $max_date->isBeforeOrEqual($empty_string),    'maxBeforeEmptyString');
		static::assertFalse(  $max_date->isBeforeOrEqual($today_string),    'maxBeforeTodayString');
		static::assertFalse(  $max_date->isBeforeOrEqual($tomorrow_string), 'maxBeforeTomorrowString');
		static::assertFalse(  $max_date->isBeforeOrEqual($empty_date),      'maxBeforeEmptyDate');
		static::assertFalse(  $max_date->isBeforeOrEqual($today_date),      'maxBeforeTodayDate');
		static::assertFalse(  $max_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		static::assertTrue (  $max_date->isBeforeOrEqual($max_date),        'maxBeforeMaxDate');
	}

	//----------------------------------------------------------------------------------- testIsEmpty
	public function testIsEmpty() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		static::assertTrue ($empty_date->isEmpty(), 'empty');
		static::assertFalse($today_date->isEmpty(), 'today');
		static::assertTrue (  $min_date->isEmpty(), 'min');
		static::assertTrue (  $max_date->isEmpty(), 'max');
	}

	//------------------------------------------------------------------------------------- testIsMax
	public function testIsMax() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		static::assertFalse($empty_date->isMax(), 'empty');
		static::assertFalse($today_date->isMax(), 'today');
		static::assertFalse(  $min_date->isMax(), 'min');
		static::assertTrue (  $max_date->isMax(), 'max');
	}

	//------------------------------------------------------------------------------------- testIsMin
	public function testIsMin() : void
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		static::assertTrue ($empty_date->isMin(), 'empty');
		static::assertFalse($today_date->isMin(), 'today');
		static::assertTrue (  $min_date->isMin(), 'min');
		static::assertFalse(  $max_date->isMin(), 'max');
	}

	//------------------------------------------------------------------------------------ testLatest
	public function testLatest() : void
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		 static::assertEquals($later, $earlier->latest($later),       'one argument');
		 static::assertEquals($later, $earlier->latest($later, $now), 'two arguments');
		 static::assertEquals($later, $earlier->latest($now, $later), 'reverse arguments');
		 static::assertEquals($later, $now->latest($earlier, $later), 'another reverse');
		 static::assertEquals($later, $now->latest($later, $earlier), 'another 2');
		 static::assertEquals($later, $later->latest($earlier, $now), 'another 3');
		 static::assertEquals($later, $later->latest($now, $earlier), 'another 4');
	}

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$month = (new Date_Time('2016-06-04 12:35:00'))
			->toBeginOf(Date_Time::MONTH)->format('Y-m-d H:i:s');
		static::assertEquals('2016-06-01 00:00:00', $month);
	}

	//---------------------------------------------------------------------------------- testTomorrow
	public function testTomorrow() : void
	{
		$today    = Date_Time::today();
		$tomorrow = Date_Time::tomorrow();
		$actual   = $today->diff($tomorrow);

		$expected = '1 day 0 hour 0 minute 0 second';

		static::assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
	}

	//--------------------------------------------------------------------------------- testYesterday
	/**
	 * Test method Date_Time::yesterday().
	 */
	public function testYesterday() : void
	{
		$today     = Date_Time::today();
		$yesterday = Date_Time::yesterday();
		$actual    = $today->diff($yesterday);

		$expected = '-1 day 0 hour 0 minute 0 second';

		static::assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
	}

}
