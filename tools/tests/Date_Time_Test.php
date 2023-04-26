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
		self::assertEquals($expected, $base->add(...$args));
	}

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct() : void
	{
		$date = new Date_Time('2016-11-05T19:46:32.56');
		self::assertEquals('2016-11-05 19:46:32', $date->format('Y-m-d H:i:s'));
	}

	//-------------------------------------------------------------------------------------- testDiff
	public function testDiff() : void
	{
		$yesterday = new Date_Time('2016-11-01 16:11:00');
		$tomorrow  = new Date_Time('2016-11-03 15:10:49');

		// Chronological
		$actual = $yesterday->diff($tomorrow);
		self::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		self::assertEquals(0, $actual->invert);

		// Chronological absolute
		$actual = $yesterday->diff($tomorrow, true);
		self::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		self::assertEquals(0, $actual->invert);

		// Reverse
		$actual = $tomorrow->diff($yesterday);
		self::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		self::assertEquals(1, $actual->invert);

		// Reverse absolute
		$actual = $tomorrow->diff($yesterday, true);
		self::assertEquals('P0Y0M1DT22H59M49S', $actual->format(Date_Interval::FULL_FORMAT));
		self::assertEquals(0, $actual->invert);
	}

	//---------------------------------------------------------------------------------- testEarliest
	public function testEarliest() : void
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		 self::assertEquals($earlier, $earlier->earliest($later),       'one argument');
		 self::assertEquals($earlier, $earlier->earliest($later, $now), 'two arguments');
		 self::assertEquals($earlier, $earlier->earliest($now, $later), 'reverse arguments');
		 self::assertEquals($earlier, $now->earliest($earlier, $later), 'another reverse');
		 self::assertEquals($earlier, $now->earliest($later, $earlier), 'another 2');
		 self::assertEquals($earlier, $later->earliest($earlier, $now), 'another 3');
		 self::assertEquals($earlier, $later->earliest($now, $earlier), 'another 4');
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
		self::assertTrue ($empty_date->is($empty_string),    'emptyIsEmptyString');
		self::assertFalse($empty_date->is($today_string),    'emptyIsTodayString');
		self::assertFalse($empty_date->is($tomorrow_string), 'emptyIsTomorrow');
		self::assertTrue ($empty_date->is($empty_date),      'emptyIsEmptyDate');
		self::assertFalse($empty_date->is($today_date),      'emptyIsTodayDate');
		self::assertTrue ($empty_date->is($min_date),        'emptyIsMinDate');
		self::assertFalse($empty_date->is($max_date),        'emptyIsMaxDate');
		self::assertFalse($today_date->is($empty_string),    'todayIsEmptyString');
		self::assertTrue ($today_date->is($today_string),    'todayIsTodayString');
		self::assertFalse($today_date->is($tomorrow_string), 'todayIsTomorrowString');
		self::assertFalse($today_date->is($empty_date),      'todayIsEmptyDate');
		self::assertFalse($today_date->is($min_date),        'todayIsMinDate');
		self::assertTrue ($today_date->is($today_date),      'todayIsTodayDate');
		self::assertFalse($today_date->is($max_date),        'todayIsMaxDate');
		self::assertTrue (  $min_date->is($empty_string),    'minIsEmptyString');
		self::assertFalse(  $min_date->is($today_string),    'minIsTodayString');
		self::assertFalse(  $min_date->is($tomorrow_string), 'minIsTomorrowString');
		self::assertTrue (  $min_date->is($empty_date),      'minIsEmptyDate');
		self::assertFalse(  $min_date->is($today_date),      'minIsTodayDate');
		self::assertTrue (  $min_date->is($min_date),        'minIsMinDate');
		self::assertFalse(  $min_date->is($max_date),        'minIsMaxDate');
		self::assertFalse(  $max_date->is($empty_string),    'maxIsEmptyString');
		self::assertFalse(  $max_date->is($today_string),    'maxIsTodayString');
		self::assertFalse(  $max_date->is($tomorrow_string), 'maxIsTomorrowString');
		self::assertFalse(  $max_date->is($empty_date),      'maxIsEmptyDate');
		self::assertFalse(  $max_date->is($today_date),      'maxIsTodayDate');
		self::assertFalse(  $max_date->is($min_date),        'minIsMinDate');
		self::assertTrue (  $max_date->is($max_date),        'maxIsMaxDate');
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
		self::assertFalse($empty_date->isAfter($empty_string),    'emptyAfterEmptyString');
		self::assertFalse($empty_date->isAfter($today_string),    'emptyAfterTodayString');
		self::assertFalse($empty_date->isAfter($tomorrow_string), 'emptyAfterTomorrow');
		self::assertFalse($empty_date->isAfter($empty_date),      'emptyAfterEmptyDate');
		self::assertFalse($empty_date->isAfter($today_date),      'emptyAfterTodayDate');
		self::assertFalse($empty_date->isAfter($min_date),        'emptyAfterMinDate');
		self::assertFalse($empty_date->isAfter($max_date),        'emptyAfterMaxDate');
		self::assertTrue ($today_date->isAfter($empty_string),    'todayAfterEmptyString');
		self::assertFalse($today_date->isAfter($today_string),    'todayAfterTodayString');
		self::assertFalse($today_date->isAfter($tomorrow_string), 'todayAfterTomorrowString');
		self::assertTrue ($today_date->isAfter($empty_date),      'todayAfterEmptyDate');
		self::assertTrue ($today_date->isAfter($min_date),        'todayAfterMinDate');
		self::assertFalse($today_date->isAfter($today_date),      'todayAfterTodayDate');
		self::assertFalse($today_date->isAfter($max_date),        'todayAfterMaxDate');
		self::assertFalse(  $min_date->isAfter($empty_string),    'minAfterEmptyString');
		self::assertFalse(  $min_date->isAfter($today_string),    'minAfterTodayString');
		self::assertFalse(  $min_date->isAfter($tomorrow_string), 'minAfterTomorrowString');
		self::assertFalse(  $min_date->isAfter($empty_date),      'minAfterEmptyDate');
		self::assertFalse(  $min_date->isAfter($today_date),      'minAfterTodayDate');
		self::assertFalse(  $min_date->isAfter($min_date),        'minAfterMinDate');
		self::assertFalse(  $min_date->isAfter($max_date),        'minAfterMaxDate');
		self::assertTrue (  $max_date->isAfter($empty_string),    'maxAfterEmptyString');
		self::assertTrue (  $max_date->isAfter($today_string),    'maxAfterTodayString');
		self::assertTrue (  $max_date->isAfter($tomorrow_string), 'maxAfterTomorrowString');
		self::assertTrue (  $max_date->isAfter($empty_date),      'maxAfterEmptyDate');
		self::assertTrue (  $max_date->isAfter($today_date),      'maxAfterTodayDate');
		self::assertTrue (  $max_date->isAfter($min_date),        'minAfterMinDate');
		self::assertFalse(  $max_date->isAfter($max_date),        'maxAfterMaxDate');
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
		self::assertTrue ($empty_date->isAfterOrEqual($empty_string),    'emptyAfterEmptyString');
		self::assertFalse($empty_date->isAfterOrEqual($today_string),    'emptyAfterTodayString');
		self::assertFalse($empty_date->isAfterOrEqual($tomorrow_string), 'emptyAfterTomorrow');
		self::assertTrue ($empty_date->isAfterOrEqual($empty_date),      'emptyAfterEmptyDate');
		self::assertFalse($empty_date->isAfterOrEqual($today_date),      'emptyAfterTodayDate');
		self::assertTrue ($empty_date->isAfterOrEqual($min_date),        'emptyAfterMinDate');
		self::assertFalse($empty_date->isAfterOrEqual($max_date),        'emptyAfterMaxDate');
		self::assertTrue ($today_date->isAfterOrEqual($empty_string),    'todayAfterEmptyString');
		self::assertTrue ($today_date->isAfterOrEqual($today_string),    'todayAfterTodayString');
		self::assertFalse($today_date->isAfterOrEqual($tomorrow_string), 'todayAfterTomorrowString');
		self::assertTrue ($today_date->isAfterOrEqual($empty_date),      'todayAfterEmptyDate');
		self::assertTrue ($today_date->isAfterOrEqual($min_date),        'todayAfterMinDate');
		self::assertTrue ($today_date->isAfterOrEqual($today_date),      'todayAfterTodayDate');
		self::assertFalse($today_date->isAfterOrEqual($max_date),        'todayAfterMaxDate');
		self::assertTrue (  $min_date->isAfterOrEqual($empty_string),    'minAfterEmptyString');
		self::assertFalse(  $min_date->isAfterOrEqual($today_string),    'minAfterTodayString');
		self::assertFalse(  $min_date->isAfterOrEqual($tomorrow_string), 'minAfterTomorrowString');
		self::assertTrue (  $min_date->isAfterOrEqual($empty_date),      'minAfterEmptyDate');
		self::assertFalse(  $min_date->isAfterOrEqual($today_date),      'minAfterTodayDate');
		self::assertTrue (  $min_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		self::assertFalse(  $min_date->isAfterOrEqual($max_date),        'minAfterMaxDate');
		self::assertTrue (  $max_date->isAfterOrEqual($empty_string),    'maxAfterEmptyString');
		self::assertTrue (  $max_date->isAfterOrEqual($today_string),    'maxAfterTodayString');
		self::assertTrue (  $max_date->isAfterOrEqual($tomorrow_string), 'maxAfterTomorrowString');
		self::assertTrue (  $max_date->isAfterOrEqual($empty_date),      'maxAfterEmptyDate');
		self::assertTrue (  $max_date->isAfterOrEqual($today_date),      'maxAfterTodayDate');
		self::assertTrue (  $max_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		self::assertTrue (  $max_date->isAfterOrEqual($max_date),        'maxAfterMaxDate');
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
		self::assertFalse($empty_date->isBefore($empty_string),    'emptyBeforeEmptyString');
		self::assertTrue ($empty_date->isBefore($today_string),    'emptyBeforeTodayString');
		self::assertTrue ($empty_date->isBefore($tomorrow_string), 'emptyBeforeTomorrow');
		self::assertFalse($empty_date->isBefore($empty_date),      'emptyBeforeEmptyDate');
		self::assertTrue ($empty_date->isBefore($today_date),      'emptyBeforeTodayDate');
		self::assertFalse($empty_date->isBefore($min_date),        'emptyBeforeMinDate');
		self::assertTrue ($empty_date->isBefore($max_date),        'emptyBeforeMaxDate');
		self::assertFalse($today_date->isBefore($empty_string),    'todayBeforeEmptyString');
		self::assertFalse($today_date->isBefore($today_string),    'todayBeforeTodayString');
		self::assertTrue ($today_date->isBefore($tomorrow_string), 'todayBeforeTomorrowString');
		self::assertFalse($today_date->isBefore($empty_date),      'todayBeforeEmptyDate');
		self::assertFalse($today_date->isBefore($min_date),        'todayBeforeMinDate');
		self::assertFalse($today_date->isBefore($today_date),      'todayBeforeTodayDate');
		self::assertTrue ($today_date->isBefore($max_date),        'todayBeforeMaxDate');
		self::assertFalse(  $min_date->isBefore($empty_string),    'minBeforeEmptyString');
		self::assertTrue (  $min_date->isBefore($today_string),    'minBeforeTodayString');
		self::assertTrue (  $min_date->isBefore($tomorrow_string), 'minBeforeTomorrowString');
		self::assertFalse(  $min_date->isBefore($empty_date),      'minBeforeEmptyDate');
		self::assertTrue (  $min_date->isBefore($today_date),      'minBeforeTodayDate');
		self::assertFalse(  $min_date->isBefore($min_date),        'minBeforeMinDate');
		self::assertTrue (  $min_date->isBefore($max_date),        'minBeforeMaxDate');
		self::assertFalse(  $max_date->isBefore($empty_string),    'maxBeforeEmptyString');
		self::assertFalse(  $max_date->isBefore($today_string),    'maxBeforeTodayString');
		self::assertFalse(  $max_date->isBefore($tomorrow_string), 'maxBeforeTomorrowString');
		self::assertFalse(  $max_date->isBefore($empty_date),      'maxBeforeEmptyDate');
		self::assertFalse(  $max_date->isBefore($today_date),      'maxBeforeTodayDate');
		self::assertFalse(  $max_date->isBefore($min_date),        'minBeforeMinDate');
		self::assertFalse(  $max_date->isBefore($max_date),        'maxBeforeMaxDate');
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
		self::assertTrue ($empty_date->isBeforeOrEqual($empty_string),    'emptyBeforeEmptyString');
		self::assertTrue ($empty_date->isBeforeOrEqual($today_string),    'emptyBeforeTodayString');
		self::assertTrue ($empty_date->isBeforeOrEqual($tomorrow_string), 'emptyBeforeTomorrow');
		self::assertTrue ($empty_date->isBeforeOrEqual($empty_date),      'emptyBeforeEmptyDate');
		self::assertTrue ($empty_date->isBeforeOrEqual($today_date),      'emptyBeforeTodayDate');
		self::assertTrue ($empty_date->isBeforeOrEqual($min_date),        'emptyBeforeMinDate');
		self::assertTrue ($empty_date->isBeforeOrEqual($max_date),        'emptyBeforeMaxDate');
		self::assertFalse($today_date->isBeforeOrEqual($empty_string),    'todayBeforeEmptyString');
		self::assertTrue ($today_date->isBeforeOrEqual($today_string),    'todayBeforeTodayString');
		self::assertTrue ($today_date->isBeforeOrEqual($tomorrow_string), 'todayBeforeTomorrowString');
		self::assertFalse($today_date->isBeforeOrEqual($empty_date),      'todayBeforeEmptyDate');
		self::assertFalse($today_date->isBeforeOrEqual($min_date),        'todayBeforeMinDate');
		self::assertTrue ($today_date->isBeforeOrEqual($today_date),      'todayBeforeTodayDate');
		self::assertTrue ($today_date->isBeforeOrEqual($max_date),        'todayBeforeMaxDate');
		self::assertTrue (  $min_date->isBeforeOrEqual($empty_string),    'minBeforeEmptyString');
		self::assertTrue (  $min_date->isBeforeOrEqual($today_string),    'minBeforeTodayString');
		self::assertTrue (  $min_date->isBeforeOrEqual($tomorrow_string), 'minBeforeTomorrowString');
		self::assertTrue (  $min_date->isBeforeOrEqual($empty_date),      'minBeforeEmptyDate');
		self::assertTrue (  $min_date->isBeforeOrEqual($today_date),      'minBeforeTodayDate');
		self::assertTrue (  $min_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		self::assertTrue (  $min_date->isBeforeOrEqual($max_date),        'minBeforeMaxDate');
		self::assertFalse(  $max_date->isBeforeOrEqual($empty_string),    'maxBeforeEmptyString');
		self::assertFalse(  $max_date->isBeforeOrEqual($today_string),    'maxBeforeTodayString');
		self::assertFalse(  $max_date->isBeforeOrEqual($tomorrow_string), 'maxBeforeTomorrowString');
		self::assertFalse(  $max_date->isBeforeOrEqual($empty_date),      'maxBeforeEmptyDate');
		self::assertFalse(  $max_date->isBeforeOrEqual($today_date),      'maxBeforeTodayDate');
		self::assertFalse(  $max_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		self::assertTrue (  $max_date->isBeforeOrEqual($max_date),        'maxBeforeMaxDate');
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
		self::assertTrue ($empty_date->isEmpty(), 'empty');
		self::assertFalse($today_date->isEmpty(), 'today');
		self::assertTrue (  $min_date->isEmpty(), 'min');
		self::assertTrue (  $max_date->isEmpty(), 'max');
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
		self::assertFalse($empty_date->isMax(), 'empty');
		self::assertFalse($today_date->isMax(), 'today');
		self::assertFalse(  $min_date->isMax(), 'min');
		self::assertTrue (  $max_date->isMax(), 'max');
	}

	//------------------------------------------------------------------------------------- testIsMin
	public function testIsMin() : void
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		self::assertTrue ($empty_date->isMin(), 'empty');
		self::assertFalse($today_date->isMin(), 'today');
		self::assertTrue (  $min_date->isMin(), 'min');
		self::assertFalse(  $max_date->isMin(), 'max');
	}

	//------------------------------------------------------------------------------------ testLatest
	public function testLatest() : void
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		 self::assertEquals($later, $earlier->latest($later),       'one argument');
		 self::assertEquals($later, $earlier->latest($later, $now), 'two arguments');
		 self::assertEquals($later, $earlier->latest($now, $later), 'reverse arguments');
		 self::assertEquals($later, $now->latest($earlier, $later), 'another reverse');
		 self::assertEquals($later, $now->latest($later, $earlier), 'another 2');
		 self::assertEquals($later, $later->latest($earlier, $now), 'another 3');
		 self::assertEquals($later, $later->latest($now, $earlier), 'another 4');
	}

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$month = (new Date_Time('2016-06-04 12:35:00'))
			->toBeginOf(Date_Time::MONTH)->format('Y-m-d H:i:s');
		self::assertEquals('2016-06-01 00:00:00', $month);
	}

	//---------------------------------------------------------------------------------- testTomorrow
	public function testTomorrow() : void
	{
		$today    = Date_Time::today();
		$tomorrow = Date_Time::tomorrow();
		$actual   = $today->diff($tomorrow);

		$expected = '1 day 0 hour 0 minute 0 second';

		self::assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
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

		self::assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
	}

}
