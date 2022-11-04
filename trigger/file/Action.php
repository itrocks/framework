<?php
namespace ITRocks\Framework\Trigger\File;

use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Action\Status;

/**
 * Action for change
 *
 * @business false
 * @override next @user invisible
 * @override request_identifier @user invisible
 * @override status @user invisible
 */
class Action extends Trigger\Action
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * TODO replace this by an @override of status with STATIC as default value
	 * Needs that the maintainer detects @business false well (this is not the case today)
	 *
	 * @param $action string|null
	 * @param $next   Date_Time|null
	 */
	public function __construct(string $action = null, Date_Time $next = null)
	{
		parent::__construct($action, $next);
		if (($this->status === status::PENDING) || !isset($this->status)) {
			$this->status = Status::STATIC;
		}
	}

}
