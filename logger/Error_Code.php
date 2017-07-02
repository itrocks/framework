<?php
namespace ITRocks\Framework\Logger;

/**
 * Logger error code constants
 */
abstract class Error_Code
{

	//----------------------------------------------------------------------------------------- CRASH
	/**
	 * The script crashed without writing an error code
	 * This code is set manually by the application administrator, when he solved the problem
	 */
	const CRASH = 2;

	//----------------------------------------------------------------------------------------- ERROR
	/**
	 * An error detected and fired by the script
	 * The script ended correctly, but there is an error to solve
	 */
	const ERROR = 1;

	//-------------------------------------------------------------------------------------------- OK
	/**
	 * The scripts runs correctly and has no problem
	 * If a stop date is set, the script ended correctly without throwing any error
	 * If a stop date is not set and the script is not running anymore : then it crashes. The
	 * application administrator must solve the problem, change the error code to 2, and set the
	 * stop date equals to the start date (or the known crashing date-time).
	 */
	const OK = 0;

	//--------------------------------------------------------------------------------------- RUNNING
	/**
	 * The script is running in "resume" mode : this is used for long-time scripts to avoid
	 * problem detection :
	 * Set by Entry::resume() : the stop date and duration are set, but the script is still running
	 * When the script will really end, this will come back to 0.
	 * If a script stopped with the code 3, then it crashed : the application administrator must solve
	 * the problem, and change the error code to 2. The stop date and duration are already set by
	 * the last resume() and do not need to be changed.
	 */
	const RUNNING = -1;

}
