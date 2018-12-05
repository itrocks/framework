<?php
namespace ITRocks\Framework\Tools\Tests;

use DateInterval;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Interval;
use ITRocks\Framework\Tools\Date_Interval_Exception;

/**
 * Date_Interval tests
 */
class Date_Interval_Test extends Test
{

	//------------------------------------------------------------------------------------ testAdjust
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @dataProvider testAdjustProvider
	 * @param $hour            string
	 * @param $invert          integer
	 * @param $expected_format string
	 * @param $expected_invert integer
	 * @see Date_Interval::adjust()
	 */
	public function testAdjust($hour, $invert, $expected_format, $expected_invert)
	{
		// Minus one hour interval
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$interval         = new DateInterval(Date_Interval::EMPTY_SPEC);
		$interval->h      = $hour;
		$interval->invert = $invert;

		$interval = Date_Interval::adjust($interval);
		static::assertEquals($expected_format, $interval->format(Date_Interval::FULL_FORMAT));
		static::assertEquals($expected_invert, $interval->invert);
	}

	//---------------------------------------------------------------------------- testAdjustProvider
	/**
	 * @return array
	 * @see Date_Interval_Test::testAdjust()
	 */
	public function testAdjustProvider()
	{
		return [
			'25 hours'           => [25,  0, 'P0Y0M1DT1H0M0S', 0],
			'25 hours inverted'  => [25,  1, 'P0Y0M1DT1H0M0S', 1],
			'-25 hours'          => [-25, 0, 'P0Y0M1DT1H0M0S', 1],
			'-25 hours inverted' => [-25, 1, 'P0Y0M1DT1H0M0S', 0],
		];
	}

	//------------------------------------------------------------------------------ testFromDuration
	/**
	 * @dataProvider testFromDurationData
	 * @param $duration        integer
	 * @param $expected_format string
	 * @param $expected_invert integer
	 * @see Date_Interval::fromDuration()
	 */
	public function testFromDuration($duration, $expected_format, $expected_invert)
	{
		$interval = Date_Interval::fromDuration($duration);
		static::assertEquals($expected_format, $interval->format(Date_Interval::FULL_FORMAT));
		static::assertEquals($expected_invert, $interval->invert);
	}

	//-------------------------------------------------------------------------- testFromDurationData
	/**
	 * Provider for Date_Interval_Test::testDays
	 *
	 * @return array
	 */
	public function testFromDurationData()
	{
		return [
			'Zero'                         => [0, 'P0Y0M0DT0H0M0S', 0],
			'One day and 10 seconds'       => [86400 + 10, 'P0Y0M1DT0H0M10S', 0],
			'Minus one day and 10 seconds' => [-(86400 + 10), 'P0Y0M1DT0H0M10S', 1],
			'2000 years 1 hour 25 seconds' => [2000 * 365 * 86400 + 3600 + 25, 'P0Y0M730000DT1H0M25S', 0]
		];
	}

	//------------------------------------------------------------------------------------ testToDays
	/**
	 * @dataProvider testToDaysProvider
	 * @param $duration integer
	 * @param $expected integer
	 * @param $round    string
	 * @see Date_Interval::toDays()
	 * @throws Date_Interval_Exception
	 */
	public function testToDays($duration, $expected, $round)
	{
		static::assertEquals(
			$expected, Date_Interval::toDays(Date_Interval::fromDuration($duration), $round)
		);
	}

	//----------------------------------------------------------------------------- testToDaysIllegal
	/**
	 * @see Date_Interval::toDays()
	 * @throws Date_Interval_Exception
	 */
	public function testToDaysIllegal()
	{
		$this->expectException(Date_Interval_Exception::class);
		$this->expectExceptionMessage(Date_Interval_Exception::MESSAGE);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$interval = new DateInterval('P1Y');
		Date_Interval::toDays($interval);
	}

	//---------------------------------------------------------------------------- testToDaysProvider
	/**
	 * @return array
	 * @see Date_Interval_Test::testToDays()
	 */
	public function testToDaysProvider()
	{
		return [
			[  86400,  1, PHP_CEIL ],
			[  86400,  1, PHP_FLOOR],
			[  86400,  1, null     ],
			[ -86400, -1, PHP_CEIL ],
			[ -86400, -1, PHP_FLOOR],
			[ -86400, -1, null     ],
			[  86401,  2, PHP_CEIL ],
			[  86401,  1, PHP_FLOOR],
			[  86401,  1, null     ],
			[ 129601,  2, null     ],
			[ -86401, -1, PHP_CEIL ],
			[ -86401, -2, PHP_FLOOR],
			[ -86401, -1, null     ],
			[-129601, -2, null     ]
		];
	}

	//----------------------------------------------------------------------------------- testToHours
	/**
	 * @see Date_Interval::toHours()
	 */
	public function testToHours()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			25, Date_Interval::toHours(Date_Interval::fromDuration(86401), PHP_CEIL)
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			24, Date_Interval::toHours(Date_Interval::fromDuration(86401), PHP_FLOOR)
		);
	}

	//--------------------------------------------------------------------------------- testToMinutes
	/**
	 * @see Date_Interval::toMinutes()
	 */
	public function testToMinutes()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			61, Date_Interval::toMinutes(Date_Interval::fromDuration(3601), PHP_CEIL)
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			60, Date_Interval::toMinutes(Date_Interval::fromDuration(3601), PHP_FLOOR)
		);
	}

	//--------------------------------------------------------------------------------- testToSeconds
	/**
	 * @see Date_Interval::toSeconds()
	 */
	public function testToSeconds()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(86400, Date_Interval::toSeconds(Date_Interval::fromDuration(86400)));
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(-86400, Date_Interval::toSeconds(Date_Interval::fromDuration(-86400)));
	}

	//----------------------------------------------------------------------------------- testToWeeks
	/**
	 * @see Date_Interval::toWeeks()
	 */
	public function testToWeeks()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			2, Date_Interval::toWeeks(Date_Interval::fromDuration(86400 * 7 + 1), PHP_CEIL)
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		static::assertEquals(
			1, Date_Interval::toWeeks(Date_Interval::fromDuration(86400 * 7 + 1), PHP_FLOOR)
		);
	}

}
