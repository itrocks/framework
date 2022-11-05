<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Period;

/**
 * Test period features
 */
class Period_Test extends Test
{

	//-------------------------------------------------------------------------------- formatProvider
	/**
	 * @return array [[Period, string|null $format, string $expected_result]]
	 */
	public function formatProvider() : array
	{
		return [
			[
				new Period(new Date_Time('2017-01-01 00:00:00'), new Date_Time('2018-02-03 01:02:03')),
				'',
				'398 days 1 hour 2 minutes 3 seconds'
			],
			[
				new Period(new Date_Time('2017-01-01 00:00:00'), new Date_Time('2018-02-03 01:02:03')),
				'%ad %H:%I:%S',
				'398d 01:02:03'
			]
		];
	}

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct() : void
	{
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date1 = new Date_Time('2016-05-03 12:05:15');
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date2 = new Date_Time('2015-06-08 13:02:00');
		static::assertEquals(new Period($date2, $date1), new Period($date1, $date2));
	}

	//----------------------------------------------------------------------------------- testExclude
	public function testExclude() : void
	{
		$date1  = new Date_Time('2016-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date2b = (new Date_Time($date2))->add(-1, Date_Time::SECOND);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date2c = (new Date_Time($date2))->add(1, Date_Time::SECOND);
		$date3  = new Date_Time('2016-06-09 10:00:00');
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date3b = (new Date_Time($date3))->add(-1, Date_Time::SECOND);
		/** @noinspection PhpUnhandledExceptionInspection valid constant */
		$date3c = (new Date_Time($date3))->add(1, Date_Time::SECOND);
		/** @noinspection DuplicatedCode I don't mind */
		$date4  = new Date_Time('2016-06-09 10:05:00');
		$date5  = new Date_Time('2016-06-09 10:05:01');
		$begin  = new Period($date1, $date2);
		$begin2 = new Period($date1, $date2b);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date5);
		$end2   = new Period($date3c, $date5);
		$full   = new Period($date1, $date5);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date5);
		$large3 = new Period($date2, $date4);
		$second = new Period($date5, $date5);
		static::assertEquals([$begin2, $end2], $full->exclude($middle), 'inside');
		static::assertEquals([new Period($date2c, $date5)], $full->exclude($begin), 'in1');
		static::assertEquals([new Period($date1, $date3b)], $full->exclude($end), 'in2');
		static::assertEquals([$begin], $begin->exclude($end), 'out-before');
		static::assertEquals([$end], $end->exclude($begin), 'out-after');
		static::assertEquals([new Period($date1, $date2b)], $large1->exclude($large2), 'exclude1');
		static::assertEquals([new Period($date3c, $date5)], $large2->exclude($large1), 'exclude2');
		static::assertEquals([], $full->exclude($full), 'same');
		static::assertEquals([$large3], $large2->exclude($second), 'micro');
		static::assertEquals([$second], $large2->exclude($large3), 'micro2');
	}

	//------------------------------------------------------------------------------------ testFormat
	/**
	 * @dataProvider formatProvider
	 * @param $period          Period
	 * @param $format          string
	 * @param $expected_result string
	 */
	public function testFormat(Period $period, string $format, string $expected_result) : void
	{
		$loc_enabled = Loc::enable(false);
		static::assertEquals($expected_result, $period->format($format));
		Loc::enable($loc_enabled);
	}

	//---------------------------------------------------------------------------------------- testIn
	public function testIn() : void
	{
		/** @noinspection DuplicatedCode I don't mind */
		$date1  = new Date_Time('2015-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date4  = new Date_Time('2016-06-09 10:05:00');
		$begin  = new Period($date1, $date2);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date4);
		$full   = new Period($date1, $date4);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date4);
		static::assertEquals(true, $middle->in($full), 'in');
		static::assertEquals(true, $middle->in($middle), 'same');
		static::assertEquals(true, $begin->in($full), 'begin');
		static::assertEquals(true, $end->in($full), 'end');
		static::assertEquals(false, $begin->in($end), 'out');
		static::assertEquals(false, $large1->in($large2), 'large1');
		static::assertEquals(false, $large2->in($large1), 'large2');
		static::assertEquals(false, $full->in($middle), 'around');
		static::assertEquals(false, $full->in($begin), 'full1');
		static::assertEquals(false, $full->in($end), 'full2');
	}

	//--------------------------------------------------------------------------------- testIntersect
	public function testIntersect() : void
	{
		/** @noinspection DuplicatedCode I don't mind */
		$date1  = new Date_Time('2015-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date4  = new Date_Time('2016-06-09 10:05:00');
		$begin  = new Period($date1, $date2);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date4);
		$full   = new Period($date1, $date4);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date4);
		static::assertEquals($middle, $full->intersect($middle), 'inside');
		static::assertEquals($begin, $full->intersect($begin), 'begin');
		static::assertEquals($end, $full->intersect($end), 'end');
		static::assertEquals(null, $begin->intersect($end), 'out-before');
		static::assertEquals(null, $end->intersect($begin), 'out-after');
		static::assertEquals($middle, $large1->intersect($large2), 'intersect1');
		static::assertEquals($middle, $large2->intersect($large1), 'intersect2');
		static::assertEquals($full, $full->intersect($full), 'same');
	}

	//--------------------------------------------------------------------------------------- testOut
	public function testOut() : void
	{
		/** @noinspection DuplicatedCode I don't mind */
		$date1  = new Date_Time('2015-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date4  = new Date_Time('2016-06-09 10:05:00');
		$begin  = new Period($date1, $date2);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date4);
		$full   = new Period($date1, $date4);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date4);
		static::assertEquals(true, $begin->out($end), 'after');
		static::assertEquals(true, $end->out($begin), 'before');
		static::assertEquals(false, $middle->out($full), 'around');
		static::assertEquals(false, $middle->out($middle), 'same');
		static::assertEquals(false, $begin->out($full), 'begin');
		static::assertEquals(false, $end->out($full), 'end');
		static::assertEquals(false, $large1->out($large2), 'large1');
		static::assertEquals(false, $large2->out($large1), 'large2');
		static::assertEquals(false, $full->out($middle), 'around');
		static::assertEquals(false, $full->out($begin), 'full1');
		static::assertEquals(false, $full->out($end), 'full2');
	}

	//---------------------------------------------------------------------------------- testToMonths
	public function testToMonths() : void
	{
		$date1  = new Date_Time('2016-05-03 12:05:15');
		$date2  = new Date_Time('2015-06-08 13:02:00');
		$months = [
			new Date_Time('2015-06-01'),
			new Date_Time('2015-07-01'),
			new Date_Time('2015-08-01'),
			new Date_Time('2015-09-01'),
			new Date_Time('2015-10-01'),
			new Date_Time('2015-11-01'),
			new Date_Time('2015-12-01'),
			new Date_Time('2016-01-01'),
			new Date_Time('2016-02-01'),
			new Date_Time('2016-03-01'),
			new Date_Time('2016-04-01'),
			new Date_Time('2016-05-01')
		];
		static::assertEquals(
			$months, (new Period($date1, $date2))->toMonths(), 'several'
		);
		static::assertEquals(
			[$date1->toBeginOf(Date_Time::MONTH)], (new Period($date1, $date1))->toMonths(), 'one'
		);
	}

	//------------------------------------------------------------------------------------- testUnion
	public function testUnion() : void
	{
		$date1  = new Date_Time('2016-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date4  = new Date_Time('2016-06-09 10:05:00');
		/** @noinspection DuplicatedCode I don't mind */
		$date5  = new Date_Time('2016-06-09 10:05:01');
		$begin  = new Period($date1, $date2);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date5);
		$full   = new Period($date1, $date5);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date5);
		$large3 = new Period($date2, $date4);
		$micro  = new Period($date4, $date5);
		static::assertEquals([$full], $full->union($middle), 'inside');
		static::assertEquals([$full], $full->union($begin), 'in1');
		static::assertEquals([$full], $full->union($end), 'in2');
		static::assertEquals([$full], $large1->union($large2), 'union1');
		static::assertEquals([$full], $large2->union($large1), 'union2');
		static::assertEquals([$full], $full->union($full), 'same');
		static::assertEquals([$begin, $end], $begin->union($end), 'out-before');
		static::assertEquals([$end, $begin], $end->union($begin), 'out-after');
		static::assertEquals([$large2], $large3->union($micro), 'micro');
	}

}
