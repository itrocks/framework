<?php
namespace ITRocks\Framework\Trigger\File;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Action\Status;

/**
 * Action for change
 */
#[
	Override('next',               new User(User::INVISIBLE)),
	Override('request_identifier', new User(User::INVISIBLE)),
	Override('status',             new User(User::INVISIBLE)),
	Store(false)
]
class Action extends Trigger\Action
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * TODO replace this by an @override of status with STATIC as default value
	 * Needs that the maintainer detects #Store('false') well (this is not the case today)
	 */
	public function __construct(string $action = null, Date_Time $next = null)
	{
		parent::__construct($action, $next);
		if (($this->status === status::PENDING) || !isset($this->status)) {
			$this->status = Status::STATIC;
		}
	}

}
