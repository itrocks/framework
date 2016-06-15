<?php
namespace SAF\Framework\Functions\Tests;

use SAF\Framework\Tests\Test;

/**
 * Type functions tests
 */
class Type_Functions_Tests extends Test
{

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
