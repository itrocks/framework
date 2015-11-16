<?php
namespace SAF\Framework\Functions\Tests;

use SAF\Framework\Tests\Test;

/**
 * Array function unit tests
 */
class Array_Function_Tests extends Test
{

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
		$result = [['match 1 found', 'match 1 elem 1', 'match 1 elem 2']];
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
			'id' => [1, 2, 3],
			'id_item' => [101, 102, 103],
			'colors' => [0 => ['white'], 2 => ['white', 'red']]
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
			'id' => [1, 2, 3],
			'id_item' => [101, 102, 103],
			'colors' => [0 => ['white' => 'white'], 2 => ['white' => 'white', 'red' => 'red']]
		];
		$array = [
			['id' => 1, 'id_item' => 101, 'colors' => ['white' => 'white']],
			['id' => 2, 'id_item' => 102],
			['id' => 3, 'id_item' => 103, 'colors' => ['white' => 'white', 'red' => 'red']]
		];
		return $this->assume(__FUNCTION__, arrayFormRevert($form, false), $array);
	}

}
