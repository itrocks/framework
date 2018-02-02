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
	public function providerAdd()
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
	 * @param $args array
	 */
	public function testAdd($expected, $args)
	{
		$base = new Date_Time('2016-09-23T11:04:02');
		$this->assertEquals($expected, $base->add(...$args));
	}

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct()
	{
		$date = new Date_Time('2016-11-05T19:46:32.56');
		$this->assertEquals('2016-11-05 19:46:32', $date->format('Y-m-d H:i:s'));
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
		 $this->assertEquals($earlier, $earlier->earliest($later),       'one argument');
		 $this->assertEquals($earlier, $earlier->earliest($later, $now), 'two arguments');
		 $this->assertEquals($earlier, $earlier->earliest($now, $later), 'reverse arguments');
		 $this->assertEquals($earlier, $now->earliest($earlier, $later), 'another reverse');
		 $this->assertEquals($earlier, $now->earliest($later, $earlier), 'another 2');
		 $this->assertEquals($earlier, $later->earliest($earlier, $now), 'another 3');
		 $this->assertEquals($earlier, $later->earliest($now, $earlier), 'another 4');
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
		$this->assertTrue ($empty_date->is($empty_string),    'emptyIsEmptyString');
		$this->assertFalse($empty_date->is($today_string),    'emptyIsTodayString');
		$this->assertFalse($empty_date->is($tomorrow_string), 'emptyIsTomorrow');
		$this->assertTrue ($empty_date->is($empty_date),      'emptyIsEmptyDate');
		$this->assertFalse($empty_date->is($today_date),      'emptyIsTodayDate');
		$this->assertTrue ($empty_date->is($min_date),        'emptyIsMinDate');
		$this->assertFalse($empty_date->is($max_date),        'emptyIsMaxDate');
		$this->assertFalse($today_date->is($empty_string),    'todayIsEmptyString');
		$this->assertTrue ($today_date->is($today_string),    'todayIsTodayString');
		$this->assertFalse($today_date->is($tomorrow_string), 'todayIsTomorrowString');
		$this->assertFalse($today_date->is($empty_date),      'todayIsEmptyDate');
		$this->assertFalse($today_date->is($min_date),        'todayIsMinDate');
		$this->assertTrue ($today_date->is($today_date),      'todayIsTodayDate');
		$this->assertFalse($today_date->is($max_date),        'todayIsMaxDate');
		$this->assertTrue (  $min_date->is($empty_string),    'minIsEmptyString');
		$this->assertFalse(  $min_date->is($today_string),    'minIsTodayString');
		$this->assertFalse(  $min_date->is($tomorrow_string), 'minIsTomorrowString');
		$this->assertTrue (  $min_date->is($empty_date),      'minIsEmptyDate');
		$this->assertFalse(  $min_date->is($today_date),      'minIsTodayDate');
		$this->assertTrue (  $min_date->is($min_date),        'minIsMinDate');
		$this->assertFalse(  $min_date->is($max_date),        'minIsMaxDate');
		$this->assertFalse(  $max_date->is($empty_string),    'maxIsEmptyString');
		$this->assertFalse(  $max_date->is($today_string),    'maxIsTodayString');
		$this->assertFalse(  $max_date->is($tomorrow_string), 'maxIsTomorrowString');
		$this->assertFalse(  $max_date->is($empty_date),      'maxIsEmptyDate');
		$this->assertFalse(  $max_date->is($today_date),      'maxIsTodayDate');
		$this->assertFalse(  $max_date->is($min_date),        'minIsMinDate');
		$this->assertTrue (  $max_date->is($max_date),        'maxIsMaxDate');
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
		$this->assertFalse($empty_date->isAfter($empty_string),    'emptyAfterEmptyString');
		$this->assertFalse($empty_date->isAfter($today_string),    'emptyAfterTodayString');
		$this->assertFalse($empty_date->isAfter($tomorrow_string), 'emptyAfterTomorrow');
		$this->assertFalse($empty_date->isAfter($empty_date),      'emptyAfterEmptyDate');
		$this->assertFalse($empty_date->isAfter($today_date),      'emptyAfterTodayDate');
		$this->assertFalse($empty_date->isAfter($min_date),        'emptyAfterMinDate');
		$this->assertFalse($empty_date->isAfter($max_date),        'emptyAfterMaxDate');
		$this->assertTrue ($today_date->isAfter($empty_string),    'todayAfterEmptyString');
		$this->assertFalse($today_date->isAfter($today_string),    'todayAfterTodayString');
		$this->assertFalse($today_date->isAfter($tomorrow_string), 'todayAfterTomorrowString');
		$this->assertTrue ($today_date->isAfter($empty_date),      'todayAfterEmptyDate');
		$this->assertTrue ($today_date->isAfter($min_date),        'todayAfterMinDate');
		$this->assertFalse($today_date->isAfter($today_date),      'todayAfterTodayDate');
		$this->assertFalse($today_date->isAfter($max_date),        'todayAfterMaxDate');
		$this->assertFalse(  $min_date->isAfter($empty_string),    'minAfterEmptyString');
		$this->assertFalse(  $min_date->isAfter($today_string),    'minAfterTodayString');
		$this->assertFalse(  $min_date->isAfter($tomorrow_string), 'minAfterTomorrowString');
		$this->assertFalse(  $min_date->isAfter($empty_date),      'minAfterEmptyDate');
		$this->assertFalse(  $min_date->isAfter($today_date),      'minAfterTodayDate');
		$this->assertFalse(  $min_date->isAfter($min_date),        'minAfterMinDate');
		$this->assertFalse(  $min_date->isAfter($max_date),        'minAfterMaxDate');
		$this->assertTrue (  $max_date->isAfter($empty_string),    'maxAfterEmptyString');
		$this->assertTrue (  $max_date->isAfter($today_string),    'maxAfterTodayString');
		$this->assertTrue (  $max_date->isAfter($tomorrow_string), 'maxAfterTomorrowString');
		$this->assertTrue (  $max_date->isAfter($empty_date),      'maxAfterEmptyDate');
		$this->assertTrue (  $max_date->isAfter($today_date),      'maxAfterTodayDate');
		$this->assertTrue (  $max_date->isAfter($min_date),        'minAfterMinDate');
		$this->assertFalse(  $max_date->isAfter($max_date),        'maxAfterMaxDate');
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
		$this->assertTrue ($empty_date->isAfterOrEqual($empty_string),    'emptyAfterEmptyString');
		$this->assertFalse($empty_date->isAfterOrEqual($today_string),    'emptyAfterTodayString');
		$this->assertFalse($empty_date->isAfterOrEqual($tomorrow_string), 'emptyAfterTomorrow');
		$this->assertTrue ($empty_date->isAfterOrEqual($empty_date),      'emptyAfterEmptyDate');
		$this->assertFalse($empty_date->isAfterOrEqual($today_date),      'emptyAfterTodayDate');
		$this->assertTrue ($empty_date->isAfterOrEqual($min_date),        'emptyAfterMinDate');
		$this->assertFalse($empty_date->isAfterOrEqual($max_date),        'emptyAfterMaxDate');
		$this->assertTrue ($today_date->isAfterOrEqual($empty_string),    'todayAfterEmptyString');
		$this->assertTrue ($today_date->isAfterOrEqual($today_string),    'todayAfterTodayString');
		$this->assertFalse($today_date->isAfterOrEqual($tomorrow_string), 'todayAfterTomorrowString');
		$this->assertTrue ($today_date->isAfterOrEqual($empty_date),      'todayAfterEmptyDate');
		$this->assertTrue ($today_date->isAfterOrEqual($min_date),        'todayAfterMinDate');
		$this->assertTrue ($today_date->isAfterOrEqual($today_date),      'todayAfterTodayDate');
		$this->assertFalse($today_date->isAfterOrEqual($max_date),        'todayAfterMaxDate');
		$this->assertTrue (  $min_date->isAfterOrEqual($empty_string),    'minAfterEmptyString');
		$this->assertFalse(  $min_date->isAfterOrEqual($today_string),    'minAfterTodayString');
		$this->assertFalse(  $min_date->isAfterOrEqual($tomorrow_string), 'minAfterTomorrowString');
		$this->assertTrue (  $min_date->isAfterOrEqual($empty_date),      'minAfterEmptyDate');
		$this->assertFalse(  $min_date->isAfterOrEqual($today_date),      'minAfterTodayDate');
		$this->assertTrue (  $min_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		$this->assertFalse(  $min_date->isAfterOrEqual($max_date),        'minAfterMaxDate');
		$this->assertTrue (  $max_date->isAfterOrEqual($empty_string),    'maxAfterEmptyString');
		$this->assertTrue (  $max_date->isAfterOrEqual($today_string),    'maxAfterTodayString');
		$this->assertTrue (  $max_date->isAfterOrEqual($tomorrow_string), 'maxAfterTomorrowString');
		$this->assertTrue (  $max_date->isAfterOrEqual($empty_date),      'maxAfterEmptyDate');
		$this->assertTrue (  $max_date->isAfterOrEqual($today_date),      'maxAfterTodayDate');
		$this->assertTrue (  $max_date->isAfterOrEqual($min_date),        'minAfterMinDate');
		$this->assertTrue (  $max_date->isAfterOrEqual($max_date),        'maxAfterMaxDate');
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
		$this->assertFalse($empty_date->isBefore($empty_string),    'emptyBeforeEmptyString');
		$this->assertTrue ($empty_date->isBefore($today_string),    'emptyBeforeTodayString');
		$this->assertTrue ($empty_date->isBefore($tomorrow_string), 'emptyBeforeTomorrow');
		$this->assertFalse($empty_date->isBefore($empty_date),      'emptyBeforeEmptyDate');
		$this->assertTrue ($empty_date->isBefore($today_date),      'emptyBeforeTodayDate');
		$this->assertFalse($empty_date->isBefore($min_date),        'emptyBeforeMinDate');
		$this->assertTrue ($empty_date->isBefore($max_date),        'emptyBeforeMaxDate');
		$this->assertFalse($today_date->isBefore($empty_string),    'todayBeforeEmptyString');
		$this->assertFalse($today_date->isBefore($today_string),    'todayBeforeTodayString');
		$this->assertTrue ($today_date->isBefore($tomorrow_string), 'todayBeforeTomorrowString');
		$this->assertFalse($today_date->isBefore($empty_date),      'todayBeforeEmptyDate');
		$this->assertFalse($today_date->isBefore($min_date),        'todayBeforeMinDate');
		$this->assertFalse($today_date->isBefore($today_date),      'todayBeforeTodayDate');
		$this->assertTrue ($today_date->isBefore($max_date),        'todayBeforeMaxDate');
		$this->assertFalse(  $min_date->isBefore($empty_string),    'minBeforeEmptyString');
		$this->assertTrue (  $min_date->isBefore($today_string),    'minBeforeTodayString');
		$this->assertTrue (  $min_date->isBefore($tomorrow_string), 'minBeforeTomorrowString');
		$this->assertFalse(  $min_date->isBefore($empty_date),      'minBeforeEmptyDate');
		$this->assertTrue (  $min_date->isBefore($today_date),      'minBeforeTodayDate');
		$this->assertFalse(  $min_date->isBefore($min_date),        'minBeforeMinDate');
		$this->assertTrue (  $min_date->isBefore($max_date),        'minBeforeMaxDate');
		$this->assertFalse(  $max_date->isBefore($empty_string),    'maxBeforeEmptyString');
		$this->assertFalse(  $max_date->isBefore($today_string),    'maxBeforeTodayString');
		$this->assertFalse(  $max_date->isBefore($tomorrow_string), 'maxBeforeTomorrowString');
		$this->assertFalse(  $max_date->isBefore($empty_date),      'maxBeforeEmptyDate');
		$this->assertFalse(  $max_date->isBefore($today_date),      'maxBeforeTodayDate');
		$this->assertFalse(  $max_date->isBefore($min_date),        'minBeforeMinDate');
		$this->assertFalse(  $max_date->isBefore($max_date),        'maxBeforeMaxDate');
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
		$this->assertTrue ($empty_date->isBeforeOrEqual($empty_string),    'emptyBeforeEmptyString');
		$this->assertTrue ($empty_date->isBeforeOrEqual($today_string),    'emptyBeforeTodayString');
		$this->assertTrue ($empty_date->isBeforeOrEqual($tomorrow_string), 'emptyBeforeTomorrow');
		$this->assertTrue ($empty_date->isBeforeOrEqual($empty_date),      'emptyBeforeEmptyDate');
		$this->assertTrue ($empty_date->isBeforeOrEqual($today_date),      'emptyBeforeTodayDate');
		$this->assertTrue ($empty_date->isBeforeOrEqual($min_date),        'emptyBeforeMinDate');
		$this->assertTrue ($empty_date->isBeforeOrEqual($max_date),        'emptyBeforeMaxDate');
		$this->assertFalse($today_date->isBeforeOrEqual($empty_string),    'todayBeforeEmptyString');
		$this->assertTrue ($today_date->isBeforeOrEqual($today_string),    'todayBeforeTodayString');
		$this->assertTrue ($today_date->isBeforeOrEqual($tomorrow_string), 'todayBeforeTomorrowString');
		$this->assertFalse($today_date->isBeforeOrEqual($empty_date),      'todayBeforeEmptyDate');
		$this->assertFalse($today_date->isBeforeOrEqual($min_date),        'todayBeforeMinDate');
		$this->assertTrue ($today_date->isBeforeOrEqual($today_date),      'todayBeforeTodayDate');
		$this->assertTrue ($today_date->isBeforeOrEqual($max_date),        'todayBeforeMaxDate');
		$this->assertTrue (  $min_date->isBeforeOrEqual($empty_string),    'minBeforeEmptyString');
		$this->assertTrue (  $min_date->isBeforeOrEqual($today_string),    'minBeforeTodayString');
		$this->assertTrue (  $min_date->isBeforeOrEqual($tomorrow_string), 'minBeforeTomorrowString');
		$this->assertTrue (  $min_date->isBeforeOrEqual($empty_date),      'minBeforeEmptyDate');
		$this->assertTrue (  $min_date->isBeforeOrEqual($today_date),      'minBeforeTodayDate');
		$this->assertTrue (  $min_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		$this->assertTrue (  $min_date->isBeforeOrEqual($max_date),        'minBeforeMaxDate');
		$this->assertFalse(  $max_date->isBeforeOrEqual($empty_string),    'maxBeforeEmptyString');
		$this->assertFalse(  $max_date->isBeforeOrEqual($today_string),    'maxBeforeTodayString');
		$this->assertFalse(  $max_date->isBeforeOrEqual($tomorrow_string), 'maxBeforeTomorrowString');
		$this->assertFalse(  $max_date->isBeforeOrEqual($empty_date),      'maxBeforeEmptyDate');
		$this->assertFalse(  $max_date->isBeforeOrEqual($today_date),      'maxBeforeTodayDate');
		$this->assertFalse(  $max_date->isBeforeOrEqual($min_date),        'minBeforeMinDate');
		$this->assertTrue (  $max_date->isBeforeOrEqual($max_date),        'maxBeforeMaxDate');
	}

	//----------------------------------------------------------------------------------- testIsEmpty
	public function testIsEmpty()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->assertTrue ($empty_date->isEmpty(), 'empty');
		$this->assertFalse($today_date->isEmpty(), 'today');
		$this->assertTrue (  $min_date->isEmpty(), 'min');
		$this->assertTrue (  $max_date->isEmpty(), 'max');
	}

	//------------------------------------------------------------------------------------- testIsMax
	public function testIsMax()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->assertFalse($empty_date->isMax(), 'empty');
		$this->assertFalse($today_date->isMax(), 'today');
		$this->assertFalse(  $min_date->isMax(), 'min');
		$this->assertTrue (  $max_date->isMax(), 'max');
	}

	//------------------------------------------------------------------------------------- testIsMin
	public function testIsMin()
	{
		$empty_date = new Date_Time('0000-00-00 00:00:00');
		$today_date = new Date_Time('2016-07-13 09:47:05');
		$min_date   = Date_Time::min();
		$max_date   = Date_Time::max();
		$this->assertTrue ($empty_date->isMin(), 'empty');
		$this->assertFalse($today_date->isMin(), 'today');
		$this->assertTrue (  $min_date->isMin(), 'min');
		$this->assertFalse(  $max_date->isMin(), 'max');
	}

	//------------------------------------------------------------------------------------ testLatest
	public function testLatest()
	{
		$earlier = new Date_Time('2006-01-01 13:29:18');
		$now     = new Date_Time('2016-10-24 10:48:12');
		$later   = new Date_Time('2034-05-12 10:00:00');
		 $this->assertEquals($later, $earlier->latest($later),       'one argument');
		 $this->assertEquals($later, $earlier->latest($later, $now), 'two arguments');
		 $this->assertEquals($later, $earlier->latest($now, $later), 'reverse arguments');
		 $this->assertEquals($later, $now->latest($earlier, $later), 'another reverse');
		 $this->assertEquals($later, $now->latest($later, $earlier), 'another 2');
		 $this->assertEquals($later, $later->latest($earlier, $now), 'another 3');
		 $this->assertEquals($later, $later->latest($now, $earlier), 'another 4');
	}

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth()
	{
		$month = (new Date_Time('2016-06-04 12:35:00'))->month()->format('Y-m-d H:i:s');
		 $this->assertEquals('2016-06-01 00:00:00', $month);
	}

	//---------------------------------------------------------------------------------- testTomorrow
	public function testTomorrow()
	{
		$today     = Date_Time::today();
		$yesterday = Date_Time::tomorrow();
		$actual    = $today->diff($yesterday);

		$expected = '1 day 0 hour 0 minute 0 second';

		$this->assertEquals($expected, $actual->format('%r%d day %h hour %i minute %s second'));
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
