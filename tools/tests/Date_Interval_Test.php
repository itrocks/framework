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

	//-------------------------------------------------------------------------------- adjustProvider
	/**
	 * @return array
	 * @see testAdjust
	 */
	public function adjustProvider() : array
	{
		return [
			'25 hours'           => [25,  0, 'P0Y0M1DT1H0M0S', 0],
			'25 hours inverted'  => [25,  1, 'P0Y0M1DT1H0M0S', 1],
			'-25 hours'          => [-25, 0, 'P0Y0M1DT1H0M0S', 1],
			'-25 hours inverted' => [-25, 1, 'P0Y0M1DT1H0M0S', 0],
		];
	}

	//------------------------------------------------------------------------------ fromDurationData
	/**
	 * @return array
	 * @see testDays
	 */
	public function fromDurationData() : array
	{
		return [
			'Zero'                         => [0, 'P0Y0M0DT0H0M0S', 0],
			'One day and 10 seconds'       => [86400 + 10, 'P0Y0M1DT0H0M10S', 0],
			'Minus one day and 10 seconds' => [-(86400 + 10), 'P0Y0M1DT0H0M10S', 1],
			'2000 years 1 hour 25 seconds' => [2000 * 365 * 86400 + 3600 + 25, 'P0Y0M730000DT1H0M25S', 0]
		];
	}

	//------------------------------------------------------------------------------------ testAdjust
	/**
	 * @dataProvider adjustProvider
	 * @param $hour            integer
	 * @param $invert          integer
	 * @param $expected_format string
	 * @param $expected_invert integer
	 */
	public function testAdjust(
		int $hour, int $invert, string $expected_format, int $expected_invert
	) : void
	{
		// Minus one hour interval
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$interval         = new DateInterval(Date_Interval::EMPTY_SPEC);
		$interval->h      = $hour;
		$interval->invert = $invert;

		$interval = Date_Interval::adjust($interval);
		self::assertEquals($expected_format, $interval->format(Date_Interval::FULL_FORMAT));
		self::assertEquals($expected_invert, $interval->invert);
	}

	//------------------------------------------------------------------------------ testFromDuration
	/**
	 * @dataProvider fromDurationData
	 * @param $duration        integer
	 * @param $expected_format string
	 * @param $expected_invert integer
	 */
	public function testFromDuration(int $duration, string $expected_format, int $expected_invert)
		: void
	{
		$interval = Date_Interval::fromDuration($duration);
		self::assertEquals($expected_format, $interval->format(Date_Interval::FULL_FORMAT));
		self::assertEquals($expected_invert, $interval->invert);
	}

	//------------------------------------------------------------------------------------ testToDays
	/**
	 * @dataProvider toDaysProvider
	 * @param $duration integer
	 * @param $expected integer
	 * @param $round    ?string
	 * @throws Date_Interval_Exception
	 */
	public function testToDays(int $duration, int $expected, ?string $round) : void
	{
		$actual = $round
			? Date_Interval::toDays(Date_Interval::fromDuration($duration), $round)
			: Date_Interval::toDays(Date_Interval::fromDuration($duration));
		self::assertEquals($expected, $actual);
	}

	//----------------------------------------------------------------------------- testToDaysIllegal
	/**
	 * @throws Date_Interval_Exception
	 */
	public function testToDaysIllegal() : void
	{
		$this->expectException(Date_Interval_Exception::class);
		$this->expectExceptionMessage(Date_Interval_Exception::MESSAGE);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$interval = new DateInterval('P1Y');
		Date_Interval::toDays($interval);
	}

	//----------------------------------------------------------------------------------- testToHours
	public function testToHours() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			25, Date_Interval::toHours(Date_Interval::fromDuration(86401))
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			24, Date_Interval::toHours(Date_Interval::fromDuration(86401), PHP_FLOOR)
		);
	}

	//--------------------------------------------------------------------------------- testToMinutes
	public function testToMinutes() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			61, Date_Interval::toMinutes(Date_Interval::fromDuration(3601))
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			60, Date_Interval::toMinutes(Date_Interval::fromDuration(3601), PHP_FLOOR)
		);
	}

	//--------------------------------------------------------------------------------- testToSeconds
	public function testToSeconds() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(86400, Date_Interval::toSeconds(Date_Interval::fromDuration(86400)));
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(-86400, Date_Interval::toSeconds(Date_Interval::fromDuration(-86400)));
	}

	//----------------------------------------------------------------------------------- testToWeeks
	public function testToWeeks() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			2, Date_Interval::toWeeks(Date_Interval::fromDuration(86400 * 7 + 1))
		);
		/** @noinspection PhpUnhandledExceptionInspection valid call with duration interval */
		self::assertEquals(
			1, Date_Interval::toWeeks(Date_Interval::fromDuration(86400 * 7 + 1), PHP_FLOOR)
		);
	}

	//-------------------------------------------------------------------------------- toDaysProvider
	/**
	 * @return array
	 * @see testToDays
	 */
	public function toDaysProvider() : array
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
			[  86401,  2, null     ],
			[ 129601,  2, null     ],
			[ -86401, -1, PHP_CEIL ],
			[ -86401, -2, PHP_FLOOR],
			[ -86401, -1, null     ],
			[-129601, -1, null     ]
		];
	}

}
