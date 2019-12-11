<?php
namespace ITRocks\Framework\Trigger\Action;

/**
 * Trigger action statuses
 *
 *         1.      2.          3.                   4.             5.
 * data :                      + request_identifier + id_log_entry
 * step :  PENDING - LAUNCHING - LAUNCHED           - RUNNING      - DONE
 * error :                     > LAUNCH_ERROR                      > ERROR
 */
class Status
{

	//---------------------------------------------------------------------------- COMPLETE / RUNNING
	const COMPLETE_STATUSES = [self::DONE, self::ERROR, self::LAUNCH_ERROR];
	const RUNNING_STATUSES  = [self::LAUNCHED, self::LAUNCHING, self::PENDING, self::RUNNING];

	//------------------------------------------------------------------------------------------ DONE
	/**
	 * 5. The action is done and did not return any error
	 */
	const DONE = 'done';

	//----------------------------------------------------------------------------------------- ERROR
	/**
	 * 5E. The action is done but it returned an error :
	 * - error in log entry
	 * - no stop date-time for the log entry
	 */
	const ERROR = 'error';

	//---------------------------------------------------------------------------------- LAUNCH_ERROR
	/**
	 * 3E. If the process running did not get a process identifier, it is a launch error (stops)
	 */
	const LAUNCH_ERROR = 'launch_error';

	//-------------------------------------------------------------------------------------- LAUNCHED
	/**
	 * 3. The action has been launched and is currently running : the request identifier is available
	 */
	const LAUNCHED = 'launched';

	//------------------------------------------------------------------------------------- LAUNCHING
	/**
	 * 2. The action entered the launch process
	 */
	const LAUNCHING = 'launching';

	//--------------------------------------------------------------------------------------- PENDING
	/**
	 * 1. The action is created as 'pending' : not launched yet
	 */
	const PENDING = 'pending';

	//--------------------------------------------------------------------------------------- RUNNING
	/**
	 * 4. The action has been launched and a running log has been found into log entries : the log
	 *    identifier is available
	 */
	const RUNNING = 'running';

	//---------------------------------------------------------------------------------------- STATIC
	/**
	 * 0. The action is not planned for execution : it is a static action for configuration purpose
	 */
	const STATIC = 'static';

}
