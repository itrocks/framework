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
		$this->assume(__METHOD__, new Period($date1, $date2), new Period($date2, $date1));
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
		$this->method(__METHOD__);
		$this->assume('inside',     $full->exclude($middle),   [$begin2, $end2]);
		$this->assume('in1',        $full->exclude($begin),    [new Period($date2c, $date5)]);
		$this->assume('in2',        $full->exclude($end),      [new Period($date1, $date3b)]);
		$this->assume('out-before', $begin->exclude($end),     [$begin]);
		$this->assume('out-after',  $end->exclude($begin),     [$end]);
		$this->assume('exclude1',   $large1->exclude($large2), [new Period($date1, $date2b)]);
		$this->assume('exclude2',   $large2->exclude($large1), [new Period($date3c, $date5)]);
		$this->assume('same',       $full->exclude($full),     []);
		$this->assume('micro',      $large2->exclude($second), [$large3]);
		$this->assume('micro2',     $large2->exclude($large3), [$second]);
		$this->method('-');
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
		$this->method(__METHOD__);
		$this->assume('in',     $middle->in($full),   true);
		$this->assume('same',   $middle->in($middle), true);
		$this->assume('begin',  $begin->in($full),    true);
		$this->assume('end',    $end->in($full),      true);
		$this->assume('out',    $begin->in($end),     false);
		$this->assume('large1', $large1->in($large2), false);
		$this->assume('large2', $large2->in($large1), false);
		$this->assume('around', $full->in($middle),   false);
		$this->assume('full1',  $full->in($begin),    false);
		$this->assume('full2',  $full->in($end),      false);
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
		$this->method(__METHOD__);
		$this->assume('inside',     $full->intersect($middle),   $middle);
		$this->assume('begin',      $full->intersect($begin),    $begin);
		$this->assume('end',        $full->intersect($end),      $end);
		$this->assume('out-before', $begin->intersect($end),     null);
		$this->assume('out-after',  $end->intersect($begin),     null);
		$this->assume('intersect1', $large1->intersect($large2), $middle);
		$this->assume('intersect2', $large2->intersect($large1), $middle);
		$this->assume('same',       $full->intersect($full),     $full);

		$this->method('-');
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
		$this->method(__METHOD__);
		$this->assume('after',  $begin->out($end),     true);
		$this->assume('before', $end->out($begin),     true);
		$this->assume('around', $middle->out($full),   false);
		$this->assume('same',   $middle->out($middle), false);
		$this->assume('begin',  $begin->out($full),    false);
		$this->assume('end',    $end->out($full),      false);
		$this->assume('large1', $large1->out($large2), false);
		$this->assume('large2', $large2->out($large1), false);
		$this->assume('around', $full->out($middle),   false);
		$this->assume('full1',  $full->out($begin),    false);
		$this->assume('full2',  $full->out($end),      false);
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
		$this->method(__METHOD__);
		$this->assume('several', (new Period($date1, $date2))->toMonths(), $months);
		$this->assume('one',     (new Period($date1, $date1))->toMonths(), [$date1->month()]);
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
		$this->method(__METHOD__);
		$this->assume('inside',     $full->union($middle),   [$full]);
		$this->assume('in1',        $full->union($begin),    [$full]);
		$this->assume('in2',        $full->union($end),      [$full]);
		$this->assume('out-before', $begin->union($end),     [$begin, $end]);
		$this->assume('out-after',  $end->union($begin),     [$end, $begin]);
		$this->assume('union1',     $large1->union($large2), [$full]);
		$this->assume('union2',     $large2->union($large1), [$full]);
		$this->assume('same',       $full->union($full),     [$full]);
		$this->assume('micro',      $large3->union($micro),  [$large2]);
		$this->method('-');
	}

}
