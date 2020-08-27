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

	//------------------------------------------------------------------------------- runningFileName
	/**
	 * Calculates the current running file name (complete path)
	 *
	 * This is a little bit hard-coded, sorry :)
	 * This complies to the default way itrocks/deploy / itrocks/platform works
	 *
	 * You will find almost the same code into console_script.php's Console::runningFileName()
	 *
	 * @return string null if the feature was not launched from the console
	 * @see Console::runningFileName
	 */
	public static function runningFileName()
	{
		global $argv;
		$console_uri = $argv[1] ?? null;
		if (!$console_uri) {
			return null;
		}
		if (substr_count(__DIR__, SL) > 4) {
			[,, $vendor, $project, $environment] = explode(SL, __DIR__);
			$prepend = $vendor . '-' . $project . '-' . $environment . '-';
		}
		else {
			$prepend = '';
		}
		return '/home/tmp/'
			. $prepend . (str_replace(SL, '_', substr($console_uri, 1)) ?: 'index');
	}

}
