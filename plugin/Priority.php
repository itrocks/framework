<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Reflection\Reflection_Class;

/**
 * Plugin priorities constants
 */
abstract class Priority
{

	//---------------------------------------------------------------------------- Priority constants
	/**
	 * Ordered in priority order, from the lowest to the highest (please keep this order)
	 */
	const TOP_CORE = 'top_core';
	const CORE     = 'core';
	const LOWEST   = 'lowest';
	const LOWER    = 'lower';
	const LOW      = 'low';
	const NORMAL   = 'normal';
	const HIGH     = 'high';
	const HIGHER   = 'higher';
	const HIGHEST  = 'highest';
	const REMOVE   = 'remove';

	//----------------------------------------------------------------------------- orderedPriorities
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string[] key from 0 to N, values are the lowercase priority constant values
	 */
	public static function orderedPriorities()
	{
		return array_values((new Reflection_Class(__CLASS__))->getConstants());
	}

}
