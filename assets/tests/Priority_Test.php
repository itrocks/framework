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
	public function providerValid() : array
	{
		return [
			[true, Priority::INCLUDED],
			[true, Priority::FIRST],
			[true, Priority::LAST],
			[true, Priority::EXCLUDED],
			'Empty is not valid'    => [false, ''],
			'notValid is not valid' => [false, 'notValid'],
		];
	}

	//------------------------------------------------------------------------------------- testValid
	/** @dataProvider providerValid */
	public function testValid(bool $expected, string $value) : void
	{
		self::assertEquals($expected, Priority::valid($value));
	}

}
