<?php
namespace ITRocks\Framework\Dao\Option;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tests\Test;

/**
 * Dao options tests
 */
class Option_Test extends Test
{

	//---------------------------------------------------------------------------------- onlyProvider
	/**
	 * @return Only[]
	 * @see testOnly
	 */
	public function onlyProvider() : array
	{
		return [
			'arguments' => [Dao::only('one', 'two', 'three', 'four')    ],
			'array'     => [Dao::only(['one', 'two', 'three', 'four'])  ],
			'mixed'     => [Dao::only('one', ['two', 'three'], 'four')  ],
			'mixed2'    => [Dao::only(['one', 'two'], 'three', ['four'])],
		];
	}

	//-------------------------------------------------------------------------------------- testOnly
	/**
	 * @dataProvider onlyProvider
	 * @param $only  Only
	 */
	public function testOnly(Only $only) : void
	{
		$assume             = Dao::only([]);
		$assume->properties = ['one', 'two', 'three', 'four'];
		static::assertEquals($assume, $only);
	}

}
