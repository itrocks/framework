<?php
namespace ITRocks\Framework\Configuration;

use ITRocks\Framework\Session;

/**
 * Environment constants
 */
abstract class Environment
{

	//----------------------------------------------------------------------------------- DEVELOPMENT
	const DEVELOPMENT = 'development';

	//------------------------------------------------------------------------------------ PRODUCTION
	const PRODUCTION = 'production';

	//------------------------------------------------------------------------------------------ TEST
	const TEST = 'test';

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get currently configured environment
	 *
	 * @return string
	 */
	public static function current()
	{
		return Session::current()->environment;
	}

}
