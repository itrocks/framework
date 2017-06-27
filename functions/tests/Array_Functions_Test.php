<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Array function unit tests
 */
class Array_Functions_Test extends Test
{

	//---------------------------------------------------------------------------------- testArrayCut
	/**
	 * All tests on arrayCut()
	 *
	 * @return boolean
	 */
	public function testArrayCut()
	{
		$string = 'Word123456Last wordsTrailing things';
		$result = true;

		// a simple complete call
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'complete',
			arrayCut($string, [4, 6, 10, 15]),
			['Word', '123456', 'Last words', 'Trailing things']
		);
		$result &= $ok;

		// ignore a single character (space)
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'ignore-single',
			arrayCut($string, [4, 6, 9, 14], SP),
			['Word', '123456', 'Lastwords', 'Trailingthings']
		);
		$result &= $ok;

		// ignore multiple characters
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'ignore-multiple',
			arrayCut($string, [3, 5, 8, 14], '2o '),
			['Wrd', '13456', 'Lastwrds', 'Trailingthings']
		);
		$result &= $ok;

		// ignore the 'trailing things' string
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'missing',
			arrayCut($string, [4, 6, 10]),
			['Word', '123456', 'Last words']
		);
		$result &= $ok;

		// get the complete trailing string without having to tell its size
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'trailing',
			arrayCut($string, [4, 6, 10], '', true),
			['Word', '123456', 'Last words', 'Trailing things']
		);
		$result &= $ok;

		// get the complete trailing string without having to tell its size, case of ignored letters
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'trailing-ignore',
			arrayCut($string, [3, 5, 8], '2o ', true),
			['Wrd', '13456', 'Lastwrds', 'Trailingthings']
		);
		$result &= $ok;

		// not enough elements : we ignore the end of the string
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'not-enough',
			arrayCut($string, [4, 6]),
			['Word', '123456']
		);
		$result &= $ok;

		// more elements to cut than available into the string : ignore those too many cuts
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'more',
			arrayCut($string, [4, 6, 10, 15, 8, 3, 4]),
			['Word', '123456', 'Last words', 'Trailing things', false, false, false]
		);
		$result &= $ok;

		// the same with the last zone too big will give us the same result
		$ok = $this->assume(
			__FUNCTION__ . DOT . 'many-more',
			arrayCut($string, [4, 6, 10, 20, 8, 3, 4]),
			['Word', '123456', 'Last words', 'Trailing things', false, false, false]
		);
		$result &= $ok;

		return $result;
	}

	//--------------------------------------------------------------------------- testArrayFormRevert
	/**
	 * Simple test of arrayFormRevert()
	 *
	 * @return boolean
	 */
	public function testArrayFormRevert()
	{
		$form = [
			'id' => [1, 2, 3],
			'id_item' => [101, 102, 103]
		];
		$array = [
			['id' => 1, 'id_item' => 101],
			['id' => 2, 'id_item' => 102],
			['id' => 3, 'id_item' => 103]
		];
		return $this->assume(__FUNCTION__, arrayFormRevert($form), $array);
	}

	//-------------------------------------------------------------------- testArrayFormRevertComplex
	/**
	 * Complex well formatted form test of arrayFormRevert()
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertComplex()
	{
		$form = [
			'address' => ['address 1', ''],
			'city' => [['id' => 11, 'name' => 'NEW-YORK'], ['name' => '']],
			'id' => ['id1'],
			'types' => [['client', 'delivery', 'invoicee']]
		];
		$array = [
			[
				'address' => 'address 1',
				'city' => ['id' => 11, 'name' => 'NEW-YORK'],
				'id' => 'id1',
				'types' => ['client', 'delivery', 'invoicee']
			],
			[
				'address' => '',
				'city' => ['name' => '']
			]
		];
		return $this->assume(__FUNCTION__, arrayFormRevert($form, false), $array);
	}

	//----------------------------------------------------------------- testArrayFormRevertComplexBad
	/**
	 * Complex badly formatted form test of arrayFormRevert()
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertComplexBad()
	{
		$form = [
			'address' => ['address 1', ''],
			'city' => [['id' => 11, 'name' => 'NEW-YORK'], ['name' => '']],
			'id' => ['id1'],
			'types' => [['client' => 'on', 'delivery' => 'on', 'invoicee' => 'on']]
		];
		$array = [
			[
				'address' => 'address 1',
				'city' => ['id' => 11, 'name' => 'NEW-YORK'],
				'id' => 'id1',
				'types' => ['client' => 'on', 'delivery' => 'on', 'invoicee' => 'on']
			],
			[
				'address' => '',
				'city' => ['name' => '']
			]
		];
		return $this->assume(__FUNCTION__, arrayFormRevert($form, false), $array);
	}

	//----------------------------------------------------------------- testArrayFormRevertOfOneMatch
	/**
	 * Unit test for arrayFormRevert() call on preg_match_all() call resulting $matches
	 * with one result
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertOfOneMatch()
	{
		$matches = [['match 1 found'], ['match 1 elem 1'], ['match 1 elem 2']];
		$result  = [['match 1 found', 'match 1 elem 1', 'match 1 elem 2']];
		return $this->assume(__FUNCTION__, arrayFormRevert($matches), $result);
	}

	//------------------------------------------------------------- testArrayFormRevertOfThreeMatches
	/**
	 * Unit test for arrayFormRevert() call on preg_match_all() call resulting $matches
	 * with three results
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertOfThreeMatches()
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
		return $this->assume(__FUNCTION__, arrayFormRevert($matches), $result);
	}

	//-------------------------------------------------------------------- testArrayFormRevertWithSet
	/**
	 * Test of arrayFormRevert() with a string[] field (string set)
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertWithSet()
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
		return $this->assume(__FUNCTION__, arrayFormRevert($form), $array);
	}

	//-------------------------------------------------------------- testArrayFormRevertWithStringSet
	/**
	 * Test of arrayFormRevert() with a string[] field with string keys (string set)
	 *
	 * @return boolean
	 */
	public function testArrayFormRevertWithStringSet()
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
		return $this->assume(__FUNCTION__, arrayFormRevert($form, false), $array);
	}

}
