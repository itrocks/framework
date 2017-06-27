<?php
namespace ITRocks\Framework\Functions\Tests;

use ITRocks\Framework\Tests\Test;

/**
 * Type functions tests
 */
class Type_Functions_Test extends Test
{

	//-------------------------------------------------------------------------- STRICT_NUMERIC_TESTS
	const STRICT_NUMERIC_TESTS = [
		/* subtitle,     value,         numeric_result, integer_result, unsigned_integer_result, unsigned_numeric_result */
		['empty_string', ''           , false,          false,          false,                   false],
		[_FALSE        , false        , false,          false,          false,                   false],
		[_TRUE         , true         , false,          false,          false,                   false],
		['null'        , null         , false,          false,          false,                   false],
		['1'           , '1'          , true ,          true ,          true ,                   true ],
		['10'          , '10'         , true ,          true ,          true ,                   true ],
		['111111111111', '11111111111', true ,          true ,          true ,                   true ],
		['+1'          , '+1'         , false,          false,          false,                   false],
		['-1'          , '-1'         , true ,          true ,          false,                   false],
		['.1'          , '.1'         , true ,          false,          false,                   true ],
		['-.1'         , '-.1'        , true ,          false,          false,                   false],
		['0.1'         , '0.1'        , true ,          false,          false,                   true ],
		['1.1'         , '1.1'        , true ,          false,          false,                   true ],
		['1,1'         , '1,1'        , false,          false,          false,                   false],
		['-1.1'        , '-1.1'       , true ,          false,          false,                   false],
		['1.'          , '1.'         , true ,          false,          false,                   true ],
		['1E2'         , '1E2'        , false,          false,          false,                   false],
		['1e2'         , '1e2'        , false,          false,          false,                   false],
		['array'       , []           , false,          false,          false,                   false],
		['string'      , 'string'     , false,          false,          false,                   false],
		['0'           , '0'          , true ,          true ,          true ,                   true ],
		['(integer)0'  , 0            , true ,          true ,          true ,                   true ],
		['(float)0.0'  , 0.0          , true ,          false,          false,                   true ],
		['01'          , '01'         , false,          false,          false,                   false],
		['-01'         , '-01'        , false,          false,          false,                   false],
		['(float)-.1'  , -.1          , true ,          false,          false,                   false],
		['(integer)-1' , -1           , true ,          true,           false,                   false],
		['(float).1'   , .1           , true ,          false,          false,                   true ],
		['(integer)1'  , 1            , true ,          true,           true,                    true ]
	];

	//--------------------------------------------------------------------------- testIsStrictInteger
	/**
	 * @return boolean
	 */
	function testIsStrictInteger()
	{
		$result = true;

		foreach (self::STRICT_NUMERIC_TESTS as list($subtitle, $check,, $assume_integer)) {
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

		foreach (self::STRICT_NUMERIC_TESTS as list($subtitle, $check, $assume_numeric)) {
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

		foreach (self::STRICT_NUMERIC_TESTS as list($subtitle, $check,,, $assume_unsigned)) {
			$ok = $this->assume(
				__METHOD__ . '(' . $subtitle . ')', isStrictUnsignedInteger($check), $assume_unsigned
			);
			$result &= $ok;
		}

		return $result;
	}

	//------------------------------------------------------------------- testIsStrictUnsignedNumeric
	/**
	 * @return boolean
	 */
	function testIsStrictUnsignedNumeric()
	{
		$result = true;

		foreach (self::STRICT_NUMERIC_TESTS as list($subtitle, $check,,,, $assume_unsigned)) {
			$ok = $this->assume(
				__METHOD__ . '(' . $subtitle . ')', isStrictNumeric($check, true, false), $assume_unsigned
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
