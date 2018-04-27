<?php
namespace ITRocks\Framework\Plugin;

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

}
