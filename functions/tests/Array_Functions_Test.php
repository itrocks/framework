<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Array function unit tests
 */
class Array_Functions_Test extends Test
{

	//------------------------------------------------------------------------------ arrayCutProvider
	/**
	 * @return array
	 * @see testArrayCut
	 */
	public function arrayCutProvider() : array
	{
		return [
			'simple complete call' => [
				['Word', '123456', 'Last words', 'Trailing things'], [4, 6, 10, 15]
			],
			'ignore a single character' => [
				['Word', '123456', 'Lastwords', 'Trailingthings'], [4, 6, 9, 14], SP
			],
			'ignore multiple characters' => [
				['Wrd', '13456', 'Lastwrds', 'Trailingthings'], [3, 5, 8, 14], '2o '
			],
			'ignore the "trailing things" string' => [
				['Word', '123456', 'Last words'], [4, 6, 10]
			],
			'get the complete trailing string without having to tell its size' => [
				['Word', '123456', 'Last words', 'Trailing things'], [4, 6, 10], '', true
			],
			'get the complete trailing string without having to tell its size and ignored letters' => [
				['Wrd', '13456', 'Lastwrds', 'Trailingthings'], [3, 5, 8], '2o ', true
			],
			'not enough elements : we ignore the end of the string' => [
				['Word', '123456'], [4, 6]
			],
			'more elements to cut than available into the string : ignore those too many cuts' => [
				['Word', '123456', 'Last words', 'Trailing things', false, false, false],
				[4, 6, 10, 15, 8, 3, 4]
			],
			'the same with the last zone too big will give us the same result' => [
				['Word', '123456', 'Last words', 'Trailing things', false, false, false],
				[4, 6, 10, 20, 8, 3, 4]
			]
		];
	}

	//---------------------------------------------------------------------------------- testArrayCut
	/**
	 * All tests on arrayCut()
	 *
	 * @dataProvider arrayCutProvider
	 * @param $expected                        string[]
	 * @param $lengths                         integer[]
	 * @param $ignore_characters               boolean|string
	 * @param $get_trailing_characters_element boolean
	 */
	public function testArrayCut(
		array $expected, array $lengths, bool|string $ignore_characters = '',
		bool $get_trailing_characters_element = false
	) : void
	{
		$string = 'Word123456Last wordsTrailing things';
		static::assertEquals(
			$expected, arrayCut($string, $lengths, $ignore_characters, $get_trailing_characters_element)
		);
	}

	//--------------------------------------------------------------------------- testArrayFormRevert
	/**
	 * Simple test of arrayFormRevert()
	 */
	public function testArrayFormRevert() : void
	{
		$form = [
			'id'      => [1, 2, 3],
			'id_item' => [101, 102, 103]
		];
		$array = [
			['id' => 1, 'id_item' => 101],
			['id' => 2, 'id_item' => 102],
			['id' => 3, 'id_item' => 103]
		];
		static::assertEquals($array, arrayFormRevert($form));
	}

	//-------------------------------------------------------------------- testArrayFormRevertComplex
	/**
	 * Complex well formatted form test of arrayFormRevert()
	 */
	public function testArrayFormRevertComplex() : void
	{
		$form = [
			'address' => ['address 1', ''],
			'city'    => [['id' => 11, 'name' => 'NEW-YORK'], ['name' => '']],
			'id'      => ['id1'],
			'types'   => [['client', 'delivery', 'invoicee']]
		];
		$array = [
			[
				'address' => 'address 1',
				'city'    => ['id' => 11, 'name' => 'NEW-YORK'],
				'id'      => 'id1',
				'types'   => ['client', 'delivery', 'invoicee']
			],
			[
				'address' => '',
				'city'    => ['name' => '']
			]
		];
		static::assertEquals($array, arrayFormRevert($form, false));
	}

	//----------------------------------------------------------------- testArrayFormRevertComplexBad
	/**
	 * Complex badly formatted form test of arrayFormRevert()
	 */
	public function testArrayFormRevertComplexBad() : void
	{
		$form = [
			'address' => ['address 1', ''],
			'city'    => [['id' => 11, 'name' => 'NEW-YORK'], ['name' => '']],
			'id'      => ['id1'],
			'types'   => [['client' => 'on', 'delivery' => 'on', 'invoicee' => 'on']]
		];
		$array = [
			[
				'address' => 'address 1',
				'city'    => ['id' => 11, 'name' => 'NEW-YORK'],
				'id'      => 'id1',
				'types'   => ['client' => 'on', 'delivery' => 'on', 'invoicee' => 'on']
			],
			[
				'address' => '',
				'city'    => ['name' => '']
			]
		];
		static::assertEquals($array, arrayFormRevert($form, false));
	}

	//----------------------------------------------------------------- testArrayFormRevertOfOneMatch
	/**
	 * Unit test for arrayFormRevert() call on preg_match_all() call resulting $matches
	 * with one result
	 */
	public function testArrayFormRevertOfOneMatch() : void
	{
		$matches = [['match 1 found'], ['match 1 elem 1'], ['match 1 elem 2']];
		$result  = [['match 1 found', 'match 1 elem 1', 'match 1 elem 2']];
		static::assertEquals($result, arrayFormRevert($matches));
	}

	//------------------------------------------------------------- testArrayFormRevertOfThreeMatches
	/**
	 * Unit test for arrayFormRevert() call on preg_match_all() call resulting $matches
	 * with three results
	 */
	public function testArrayFormRevertOfThreeMatches() : void
	{
		$matches = [
			['match 1 found', 'match 2 found', 'match 3 found'],
			['match 1 elem 1', 'match 2 elem 1', 'match 3 elem 1'],
			['match 1 elem 2', 'match 2 elem 2', 'match 3 elem 2']
		];
		$result = [
			['match 1 found', 'match 1 elem 1', 'match 1 elem 2'],
			['match 2 found', 'match 2 elem 1', 'match 2 elem 2'],
			['match 3 found', 'match 3 elem 1', 'match 3 elem 2']
		];
		static::assertEquals($result, arrayFormRevert($matches));
	}

	//-------------------------------------------------------------------- testArrayFormRevertWithSet
	/**
	 * Test of arrayFormRevert() with a string[] field (string set)
	 */
	public function testArrayFormRevertWithSet() : void
	{
		$form = [
			'id'      => [1, 2, 3],
			'id_item' => [101, 102, 103],
			'colors'  => [0 => ['white'], 2 => ['white', 'red']]
		];
		$array = [
			['id' => 1, 'id_item' => 101, 'colors' => ['white']],
			['id' => 2, 'id_item' => 102],
			['id' => 3, 'id_item' => 103, 'colors' => ['white', 'red']]
		];
		static::assertEquals($array, arrayFormRevert($form));
	}

	//-------------------------------------------------------------- testArrayFormRevertWithStringSet
	/**
	 * Test of arrayFormRevert() with a string[] field with string keys (string set)
	 */
	public function testArrayFormRevertWithStringSet() : void
	{
		$form = [
			'id'      => [1, 2, 3],
			'id_item' => [101, 102, 103],
			'colors'  => [0 => ['white' => 'white'], 2 => ['white' => 'white', 'red' => 'red']]
		];
		$array = [
			['id' => 1, 'id_item' => 101, 'colors' => ['white' => 'white']],
			['id' => 2, 'id_item' => 102],
			['id' => 3, 'id_item' => 103, 'colors' => ['white' => 'white', 'red' => 'red']]
		];
		static::assertEquals($array, arrayFormRevert($form, false));
	}

}
