<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Plugin\Has_Get;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User;

/**
 * Common code for all trigger plugins
 */
abstract class Plugin implements Registerable
{
	use Has_Get;

	//-------------------------------------------------------------------------------- launchNextStep
	/**
	 * Launch next step as an action (will need a running server)
	 *
	 * @param $action_link string
	 */
	protected function launchNextStep($action_link)
	{
		$now  = Date_Time::now();
		$user = User::current();
		if (!Dao::searchOne(
			[
				'action'    => $action_link,
				'as_user'   => $user,
				'keep_user' => false,
				'next'      => Func::lessOrEqual($now),
				'status'    => Action\Status::PENDING
			],
			Action::class
		)) {
			$action          = new Action();
			$action->action  = $action_link;
			$action->as_user = $user;
			$action->next    = $now;
			Dao::write($action);
		}
	}

}
