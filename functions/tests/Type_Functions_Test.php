<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Type functions tests
 */
class Type_Functions_Test extends Test
{

	//----------------------------------------------------------------------- isStrictIntegerProvider
	/**
	 * @return array
	 */
	public function isStrictIntegerProvider()
	{
		return [
			/* subtitle,      [value,        results                                               ] */
			/*                               [integer, numeric, unsigned_integer, unsigned_numeric]  */
			'empty_string' => [''           ,[false,   false,   false,            false           ]],
			_FALSE         => [false        ,[false,   false,   false,            false           ]],
			_TRUE          => [true         ,[false,   false,   false,            false           ]],
			'null'         => [null         ,[false,   false,   false,            false           ]],
			'1'            => ['1'          ,[true ,   true ,   true ,            true            ]],
			'10'           => ['10'         ,[true ,   true ,   true ,            true            ]],
			'111111111111' => ['11111111111',[true ,   true ,   true ,            true            ]],
			'+1'           => ['+1'         ,[false,   false,   false,            false           ]],
			'-1'           => ['-1'         ,[true ,   true ,   false,            false           ]],
			'.1'           => ['.1'         ,[false,   true ,   false,            true            ]],
			'-.1'          => ['-.1'        ,[false,   true ,   false,            false           ]],
			'0.1'          => ['0.1'        ,[false,   true ,   false,            true            ]],
			'1.1'          => ['1.1'        ,[false,   true ,   false,            true            ]],
			'1,1'          => ['1,1'        ,[false,   false,   false,            false           ]],
			'-1.1'         => ['-1.1'       ,[false,   true ,   false,            false           ]],
			'1.'           => ['1.'         ,[false,   true ,   false,            true            ]],
			'1E2'          => ['1E2'        ,[false,   false,   false,            false           ]],
			'1e2'          => ['1e2'        ,[false,   false,   false,            false           ]],
			'array'        => [[]           ,[false,   false,   false,            false           ]],
			'string'       => ['string'     ,[false,   false,   false,            false           ]],
			'0'            => ['0'          ,[true ,   true ,   true ,            true            ]],
			'(integer)0'   => [0            ,[true ,   true ,   true ,            true            ]],
			'.0'           => ['.0'         ,[false,   true ,   false,            true            ]],
			'0.0'          => ['0.0'        ,[false,   true ,   false,            true            ]],
			'101.0'        => ['101.0'      ,[false,   true ,   false,            true            ]],
			'(float).0'    => [.0           ,[false,   true ,   false,            true            ]],
			'(float)101.0' => [101.0        ,[false,   true ,   false,            true            ]],
			'01'           => ['01'         ,[false,   false,   false,            false           ]],
			'-01'          => ['-01'        ,[false,   false,   false,            false           ]],
			'-0.64'        => ['-0.64'      ,[false,   true ,   false,            false           ]],
			'(float)-.64'  => [-.64         ,[false,   true ,   false,            false           ]],
			'(float)-.1'   => [-.1          ,[false,   true ,   false,            false           ]],
			'(integer)-1'  => [-1           ,[true,    true ,   false,            false           ]],
			'(float).1'    => [.1           ,[false,   true ,   false,            true            ]],
			'(integer)1'   => [1            ,[true,    true ,   true,             true            ]],
			'91.8500'      => ['91.8500'    ,[false,   true ,   false,            true            ]],
			'12.'          => ['12.'        ,[false,    true ,  false,            true            ]]
		];
	}

	//-------------------------------------------------------------------------------- maxSetProvider
	/**
	 * @see testMaxSet
	 */
	public function maxSetProvider()
	{
		return [
			'simple' => [13,[13, 4, 2]],
			'array'  => [13,[[2, 4, 13]]],
			'false'  => [13,[2, false, false, 13, 4]],
			'null'   => [13,[2, null, 13, null, 4]],
			'mix'    => [19,[3, 2, [-1, 19, false], null, [null, 9], 4]],
		];
	}

	//-------------------------------------------------------------------------------- minSetProvider
	/**
	 * @see testMinSet
	 */
	public function minSetProvider()
	{
		return [
			'simple' => [2,[13, 4, 2]],
			'array'  => [2,[[2, 4, 13]]],
			'false'  => [2,[2, false, false, 13, 4]],
			'null'   => [2,[2, null, 13, null, 4]],
			'mix'    => [-1,[3, 2, [-1, 19, false], null, [null, 9], 4]],
		];
	}

	//--------------------------------------------------------------------------- testIsStrictInteger
	/**
	 * @dataProvider isStrictIntegerProvider
	 * @param $value          mixed
	 * @param $array_expected boolean[]
	 */
	function testIsStrictInteger($value, $array_expected)
	{
		static::assertEquals($array_expected[0], isStrictInteger($value));
	}

	//--------------------------------------------------------------------------- testIsStrictNumeric
	/**
	 * @dataProvider isStrictIntegerProvider
	 * @param $value          mixed
	 * @param $array_expected boolean[]
	 */
	function testIsStrictNumeric($value, $array_expected)
	{
		static::assertEquals($array_expected[1], isStrictNumeric($value));
	}

	//------------------------------------------------------------------- testIsStrictUnsignedInteger
	/**
	 * @dataProvider isStrictIntegerProvider
	 * @param $value          mixed
	 * @param $array_expected boolean[]
	 */
	function testIsStrictUnsignedInteger($value, $array_expected)
	{
		static::assertEquals($array_expected[2], isStrictUnsignedInteger($value));
	}

	//------------------------------------------------------------------- testIsStrictUnsignedNumeric
	/**
	 * @dataProvider isStrictIntegerProvider
	 * @param $value          mixed
	 * @param $array_expected boolean[]
	 */
	function testIsStrictUnsignedNumeric($value, $array_expected)
	{
		static::assertEquals($array_expected[3], isStrictNumeric($value, true, false));
	}

	//------------------------------------------------------------------------------------ testMaxSet
	/**
	 * @dataProvider maxSetProvider
	 * @param $expected integer
	 * @param $args     mixed
	 */
	function testMaxSet($expected, $args)
	{
		static::assertEquals($expected, call_user_func('maxSet', $args));
	}

	//------------------------------------------------------------------------------------ testMinSet
	/**
	 * @dataProvider minSetProvider
	 * @param $expected integer
	 * @param $args     mixed
	 */
	function testMinSet($expected, $args)
	{
		static::assertEquals($expected, call_user_func('minSet', $args));
	}

}
