<?php
namespace SAF\Framework\Tools\Tests;

use SAF\Framework\Tests\Test;
use SAF\Framework\Tools\Date_Interval;

/**
 * Date_Interval tests
 */
class Date_Interval_Tests extends Test
{

	//------------------------------------------------------------------------ testCreateFromDuration
	public function testCreateFromDuration()
	{
		$this->assume(__METHOD__, Date_Interval::createFromDuration(0), new Date_Interval('P0D'));
	}

}
