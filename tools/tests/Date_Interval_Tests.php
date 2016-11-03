<?php
namespace ITRocks\Framework\Tools\Tests;

use ITRocks\Framework\Tests\Test;
use ITRocks\Framework\Tools\Date_Interval;

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
