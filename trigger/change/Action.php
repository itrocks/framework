<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Action\Status;

/**
 * Action for change
 *
 * @override next @user invisible
 * @override request_identifier @user invisible
 */
class Action extends Trigger\Action
{

	//--------------------------------------------------------------------------------------- $status
	/**
	 * @override
	 * @user invisible
	 */
	public $status = Status::STATIC;

}
