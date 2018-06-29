<?php
namespace ITRocks\Framework\Tests;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use PHPUnit_Framework_Error_Notice;

/**
 * All unit test classes must extend this, to access its begin(), end() and assume() methods
 */
abstract class Test extends Testable
{

	//------------------------------------------------------------------------------ FUNCTIONAL_GROUP
	const FUNCTIONAL_GROUP = 'functional';

	//---------------------------------------------------------------------------------------- assume
	/**
	 * Assumes a checked value is the same than an assumed value
	 *
	 * @deprecated use assertEquals
	 * @param $assume      mixed the assumed value
	 * @param $check       mixed the checked value
	 * @param $test        string the name of the test (ie 'Method_Name[.test_name]')
	 */
	protected function assume($test, $check, $assume)
	{
		$check  = $this->toArray($check);
		$assume = $this->toArray($assume);
		static::assertEquals($assume, $check, $test);
	}

	//----------------------------------------------------------------------------------------- setUp
	/**
	 * Changes locale for test
	 *
	 * {@inheritdoc}
	 */
	protected function setUp()
	{
		parent::setUp();
		if (array_key_exists(static::FUNCTIONAL_GROUP, array_flip($this->getGroups()))) {
			// Functional testing

			// There will be notice when modifying/creating table
			PHPUnit_Framework_Error_Notice::$enabled = FALSE;
			Dao::begin();
		}
		else {
			// Disabling translation
			Loc::$disabled = true;
			// TODO Mocking database
		}
	}

	//-------------------------------------------------------------------------------------- tearDown
	/**
	 * {@inheritdoc}
	 */
	protected function tearDown()
	{
		if (array_key_exists(static::FUNCTIONAL_GROUP, array_flip($this->getGroups()))) {
			// Functional testing

			Dao::rollback();
		}
		else {
			// Enabled translation
			Loc::$disabled = false;
		}
		parent::tearDown();
	}

	//--------------------------------------------------------------------------------------- toArray
	/**
	 * @param $array   mixed
	 * @param $already object[] objects hash table to avoid recursion
	 * @return mixed
	 */
	private function toArray($array, array $already = [])
	{
		if (is_object($array)) {
			if (isset($already[md5(spl_object_hash($array))])) {
				$array = ['__CLASS__' => get_class($array), '__RECURSE__' => null];
			}
			else {
				$already[md5(spl_object_hash($array))] = true;
				$array = ['__CLASS__' => get_class($array)]
					+ $this->toArray(get_object_vars($array), $already);
			}
		}
		if (is_array($array)) {
			foreach ($array as $key => $value) {
				$array[$key] = $this->toArray($value, $already);
			}
			return $array;
		}
		else {
			return $array;
		}
	}

}
