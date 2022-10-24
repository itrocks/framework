<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;

/**
 * The constructor of a configurable plugin must accept the configuration array as unique parameter
 */
interface Configurable extends Plugin
{

	//----------------------------------------------------------------------------------------- CLEAR
	/**
	 * Apply this constant to configuration trees to clear (unset) them
	 */
	const CLEAR = '@CLEAR';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $configuration mixed
	 */
	public function __construct(mixed $configuration);

}
