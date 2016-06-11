<?php
namespace SAF\Framework\Tools\Tests;

use SAF\Framework\Tests\Test;
use SAF\Framework\Tools\Date_Time;

/**
 * Date_Time tools class unit tests
 */
class Date_Time_Tests extends Test
{

	//----------------------------------------------------------------------------------- testToMonth
	public function testToMonth()
	{
		$month = (new Date_Time('2016-06-04 12:35:00'))->toMonth()->format('Y-m-d H:i:s');
		$this->assume(__METHOD__, $month, '2016-06-01 00:00:00');
	}

}
