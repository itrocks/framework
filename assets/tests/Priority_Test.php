<?php
namespace ITRocks\Framework\Assets\Tests;

use ITRocks\Framework\Assets\Priority;
use ITRocks\Framework\Tests\Test;

/**
 * Assets Priority Test
 */
class Priority_Test extends Test
{

	//--------------------------------------------------------------------------------- providerValid
	/**
	 * @return array[]
	 * @see Priority_Test::testValid()
	 */
	public function providerValid()
	{
		return [
			[true, Priority::INCLUDED],
			[true, Priority::FIRST],
			[true, Priority::LAST],
			[true, Priority::EXCLUDED],
			'Null is not valid'     => [false, null],
			'Empty is not valid'    => [false, ''],
			'notValid is not valid' => [false, 'notValid'],
		];
	}

	//------------------------------------------------------------------------------------- testValid
	/**
	 * @dataProvider providerValid
	 * @param $expected boolean
	 * @param $value    string
	 */
	public function testValid($expected, $value)
	{
		static::assertEquals($expected, Priority::valid($value));
	}

}
