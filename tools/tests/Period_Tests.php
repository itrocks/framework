<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Period;

/**
 * Test period features
 */
class Period_Tests extends Test
{

	//--------------------------------------------------------------------------------- testConstruct
	public function testConstruct()
	{
		$date1 = new Date_Time('2016-05-03 12:05:15');
		$date2 = new Date_Time('2015-06-08 13:02:00');
		$this->assume(__METHOD__, new Period($date1, $date2), new Period($date2, $date1));
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
		$date1 = new Date_Time('2016-05-03 12:05:15');
		$date2 = new Date_Time('2015-06-08 13:02:00');
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
			new Date_Time('2016-05-01'),
		];
		$this->method(__METHOD__);
		$this->assume('several', (new Period($date1, $date2))->toMonths(), $months);
		$this->assume('one',     (new Period($date1, $date1))->toMonths(), [$date1->month()]);
	}

}
