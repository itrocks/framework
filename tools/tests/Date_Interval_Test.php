<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Interval;

/**
 * Date_Interval tests
 */
class Date_Interval_Test extends Test
{

	//------------------------------------------------------------------------ testCreateFromDuration
	public function testCreateFromDuration()
	{

		$this->assertEquals(new Date_Interval('P0D'), Date_Interval::createFromDuration(0));
		$day10sec = 24 * 3600 + 10;

		$this->assertEquals(new Date_Interval('P1DT10S'), Date_Interval::createFromDuration($day10sec));
		$this->assertEquals(
			new Date_Interval('P1DT10S', 1), Date_Interval::createFromDuration(-$day10sec)
		);
	}

}
