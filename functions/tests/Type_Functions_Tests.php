<?php
namespace SAF\Framework\Functions\Tests;

use SAF\Framework\Tests\Test;

/**
 * Type functions tests
 */
class Type_Functions_Tests extends Test
{

	//--------------------------------------------------------------------------------- NUMERIC_TESTS
	const NUMERIC_TESTS = [
		/* subtitle,     value,         numeric_result, integer_result, unsigned_integer_result */
		['empty_string', ''           , false,          false,          false],
		['false'       , false        , false,          false,          false],
		['true'        , true         , false,          false,          false],
		['null'        , null         , false,          false,          false],
		['1'           , '1'          , true ,          true ,          true ],
		['10'          , '10'         , true ,          true ,          true ],
		['111111111111', '11111111111', true ,          true ,          true ],
		['+1'          , '+1'         , false,          false,          false],
		['-1'          , '-1'         , true ,          true ,          false],
		['.1'          , '.1'         , false,          false,          false],
		['-.1'         , '-.1'        , true ,          false,          false],
		['0.1'         , '0.1'        , false,          false,          false],
		['1.1'         , '1.1'        , true ,          false,          false],
		['1,1'         , '1,1'        , false,          false,          false],
		['-1.1'        , '-1.1'       , true,           false,          false],
		['1.'          , '1.'         , true ,          false,          false],
		['1E2'         , '1E2'        , false,          false,          false],
		['1e2'         , '1e2'        , false,          false,          false],
		['array'       , []           , false,          false,          false],
		['string'      , 'string'     , false,          false,          false],
	];

	//--------------------------------------------------------------------------- testIsStrictInteger
	/**
	 * @return boolean
	 */
	function testIsStrictInteger()
	{
		$result = true;

		foreach (
			self::NUMERIC_TESTS
			as list($subtitle, $check, $assume_numeric, $assume_integer, $assume_unsigned)
		) {
			$ok = $this->assume(
				__METHOD__ . '(' . $subtitle . ')', isStrictInteger($check), $assume_integer
			);
			$result &= $ok;
		}

		return $result;
	}

	//--------------------------------------------------------------------------- testIsStrictNumeric
	/**
	 * @return boolean
	 */
	function testIsStrictNumeric()
	{
		$result = true;

		foreach (
			self::NUMERIC_TESTS
			as list($subtitle, $check, $assume_numeric, $assume_integer, $assume_unsigned)
		) {
			$ok = $this->assume(
				__METHOD__ . '(' . $subtitle . ')', isStrictNumeric($check), $assume_numeric
			);
			$result &= $ok;
		}

		return $result;
	}

	//------------------------------------------------------------------- testIsStrictUnsignedInteger
	/**
	 * @return boolean
	 */
	function testIsStrictUnsignedInteger()
	{
		$result = true;

		foreach (
			self::NUMERIC_TESTS
			as list($subtitle, $check, $assume_numeric, $assume_integer, $assume_unsigned)
		) {
			$ok = $this->assume(
				__METHOD__ . '(' . $subtitle . ')', isStrictUnsignedInteger($check), $assume_unsigned
			);
			$result &= $ok;
		}

		return $result;
	}

	//------------------------------------------------------------------------------------ testMaxSet
	/**
	 * @return boolean
	 */
	function testMaxSet()
	{
		$result = true;

		$ok = $this->assume(__METHOD__ . '.simple', maxSet(13, 4, 2), 13);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.array', maxSet([2, 4, 13]), 13);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.false', maxSet(2, false, false, 13, 4), 13);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.null', maxSet(2, null, 13, null, 4), 13);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.mix', maxSet(3, 2, [-1, 19, false], null, [null, 9], 4), 19);
		$result &= $ok;

		return $result;
	}

	//------------------------------------------------------------------------------------ testMinSet
	/**
	 * @return boolean
	 */
	function testMinSet()
	{
		$result = true;

		$ok = $this->assume(__METHOD__ . '.simple', minSet(13, 4, 2), 2);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.array', minSet([13, 2, 4]), 2);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.false', minSet(13, 2, false, false, 4), 2);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.null', minSet(13, 2, null, null, 4), 2);
		$result &= $ok;

		$ok = $this->assume(__METHOD__ . '.mix', minSet(13, 2, [-1, 9, false], null, [null, 9], 4), -1);
		$result &= $ok;

		return $result;
	}

}
