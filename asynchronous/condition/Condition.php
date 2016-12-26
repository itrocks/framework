<?php
namespace ITRocks\Framework\Asynchronous;

/**
 * Condition to execute task
 */
abstract class Condition
{

	//----------------------------------------------------------------------------------------- VALID
	const VALID = 'valid';

	//--------------------------------------------------------------------------------------- PENDING
	const PENDING = 'pending';

	//--------------------------------------------------------------------------------- PENDING_ERROR
	const PENDING_ERROR = 'pending_error';

	//----------------------------------------------------------------------------------------- $task
	/**
	 * Main task
	 *
	 * @store false
	 * @var Task
	 */
	public $task;

	//----------------------------------------------------------------------------------------- check
	/**
	 * Check condition
	 *
	 * @return string valid, pending or pending_error (see Condition constants)
	 */
	public abstract function check();
}
