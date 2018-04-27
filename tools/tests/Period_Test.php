<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Period;

/**
 * Test period features
 */
class Period_Test extends Test
{

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct()
	{
		$date1 = new Date_Time('2016-05-03 12:05:15');
		$date2 = new Date_Time('2015-06-08 13:02:00');
		$this->assertEquals(new Period($date2, $date1), new Period($date1, $date2));
	}

	//----------------------------------------------------------------------------------- testExclude
	public function testExclude()
	{
		$date1  = new Date_Time('2016-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date2b = (new Date_Time($date2))->add(-1, Date_Time::SECOND);
		$date2c = (new Date_Time($date2))->add(1, Date_Time::SECOND);
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date3b = (new Date_Time($date3))->add(-1, Date_Time::SECOND);
		$date3c = (new Date_Time($date3))->add(1, Date_Time::SECOND);
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
		$this->assertEquals([$begin2, $end2], $full->exclude($middle), 'inside');
		$this->assertEquals([new Period($date2c, $date5)], $full->exclude($begin), 'in1');
		$this->assertEquals([new Period($date1, $date3b)], $full->exclude($end), 'in2');
		$this->assertEquals([$begin], $begin->exclude($end), 'out-before');
		$this->assertEquals([$end], $end->exclude($begin), 'out-after');
		$this->assertEquals([new Period($date1, $date2b)], $large1->exclude($large2), 'exclude1');
		$this->assertEquals([new Period($date3c, $date5)], $large2->exclude($large1), 'exclude2');
		$this->assertEquals([], $full->exclude($full), 'same');
		$this->assertEquals([$large3], $large2->exclude($second), 'micro');
		$this->assertEquals([$second], $large2->exclude($large3), 'micro2');
	}

	//------------------------------------------------------------------------------------ testFormat
	/**
	 * @dataProvider testFormatProvider
	 * @param $period          Period
	 * @param $format          string
	 * @param $expected_result string
	 */
	public function testFormat($period, $format, $expected_result)
	{
		$this->assertEquals($expected_result, $period->format($format));
	}

	//---------------------------------------------------------------------------- testFormatProvider
	/**
	 * @return array [[Period, string|null $format, string $expected_result]]
	 */
	public function testFormatProvider()
	{
		return [
			[
				new Period(new Date_Time('2017-01-01 00:00:00'), new Date_Time('2018-02-03 01:02:03')),
				null,
				'398 days 1 hour 2 minutes 3 seconds'
			],
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

	//---------------------------------------------------------------------------------------- testIn
	public function testIn()
	{
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
		$this->assertEquals(true, $middle->in($full), 'in');
		$this->assertEquals(true, $middle->in($middle), 'same');
		$this->assertEquals(true, $begin->in($full), 'begin');
		$this->assertEquals(true, $end->in($full), 'end');
		$this->assertEquals(false, $begin->in($end), 'out');
		$this->assertEquals(false, $large1->in($large2), 'large1');
		$this->assertEquals(false, $large2->in($large1), 'large2');
		$this->assertEquals(false, $full->in($middle), 'around');
		$this->assertEquals(false, $full->in($begin), 'full1');
		$this->assertEquals(false, $full->in($end), 'full2');
	}

	//--------------------------------------------------------------------------------- testIntersect
	public function testIntersect()
	{
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
		$this->assertEquals($middle, $full->intersect($middle), 'inside');
		$this->assertEquals($begin, $full->intersect($begin), 'begin');
		$this->assertEquals($end, $full->intersect($end), 'end');
		$this->assertEquals(null, $begin->intersect($end), 'out-before');
		$this->assertEquals(null, $end->intersect($begin), 'out-after');
		$this->assertEquals($middle, $large1->intersect($large2), 'intersect1');
		$this->assertEquals($middle, $large2->intersect($large1), 'intersect2');
		$this->assertEquals($full, $full->intersect($full), 'same');
	}

	//--------------------------------------------------------------------------------------- testOut
	public function testOut()
	{
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
		$this->assertEquals(true, $begin->out($end), 'after');
		$this->assertEquals(true, $end->out($begin), 'before');
		$this->assertEquals(false, $middle->out($full), 'around');
		$this->assertEquals(false, $middle->out($middle), 'same');
		$this->assertEquals(false, $begin->out($full), 'begin');
		$this->assertEquals(false, $end->out($full), 'end');
		$this->assertEquals(false, $large1->out($large2), 'large1');
		$this->assertEquals(false, $large2->out($large1), 'large2');
		$this->assertEquals(false, $full->out($middle), 'around');
		$this->assertEquals(false, $full->out($begin), 'full1');
		$this->assertEquals(false, $full->out($end), 'full2');
	}

	//---------------------------------------------------------------------------------- testToMonths
	public function testToMonths()
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
		$this->assertEquals($months, (new Period($date1, $date2))->toMonths(), 'several');
		$this->assertEquals([$date1->month()], (new Period($date1, $date1))->toMonths(), 'one');
	}

	//------------------------------------------------------------------------------------- testUnion
	public function testUnion()
	{
		$date1  = new Date_Time('2016-05-03 12:05:15');
		$date2  = new Date_Time('2016-06-08 13:02:00');
		$date3  = new Date_Time('2016-06-09 10:00:00');
		$date4  = new Date_Time('2016-06-09 10:05:00');
		$date5  = new Date_Time('2016-06-09 10:05:01');
		$begin  = new Period($date1, $date2);
		$middle = new Period($date2, $date3);
		$end    = new Period($date3, $date5);
		$full   = new Period($date1, $date5);
		$large1 = new Period($date1, $date3);
		$large2 = new Period($date2, $date5);
		$large3 = new Period($date2, $date4);
		$micro  = new Period($date4, $date5);
		$this->assertEquals([$full], $full->union($middle), 'inside');
		$this->assertEquals([$full], $full->union($begin), 'in1');
		$this->assertEquals([$full], $full->union($end), 'in2');
		$this->assertEquals([$full], $large1->union($large2), 'union1');
		$this->assertEquals([$full], $large2->union($large1), 'union2');
		$this->assertEquals([$full], $full->union($full), 'same');
		$this->assertEquals([$begin, $end], $begin->union($end), 'out-before');
		$this->assertEquals([$end, $begin], $end->union($begin), 'out-after');
		$this->assertEquals([$large2], $large3->union($micro), 'micro');
	}

}
