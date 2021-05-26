<?php
namespace ITRocks\Framework\Trigger\Schedule\user_restriction;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\Trigger;
use ITRocks\Framework\Trigger\Action\Status;
use ITRocks\Framework\Trigger\Schedule;
use ITRocks\Framework\User\Banned_Controller;

/**
 * Class Action
 */
class Action extends Trigger
{

	//------------------------------------------------------------------------------------- getAction
	/**
	 * @return string
	 */
	private function getAction() : string
	{
		return SL . Names::classToUri(Banned_Controller::class) . SL .'run';
	}

	//--------------------------------------------------------------------------------------- execute
	public function execute()
	{
		$action            = new Trigger\Action();
		$action->action    = $this->getAction();
		$action->status    = Status::STATIC;
		$schedule          = new Schedule();
		$schedule->name    = 'Ban User';
		$schedule->actions = [$action];
		Dao::write($schedule);
	}

	//------------------------------------------------------------------------------------------ stop
	public function stop()
	{
		Dao::begin();
		foreach (Dao::search(['action' => $this->getAction()], Trigger\Action::class) as $action) {
			foreach (Dao::search(['actions' => $action], Schedule::class) as $schedule) {
				if (count($schedule->actions) === 1) {
					Dao::delete($schedule);
				}
			}
			Dao::delete($action);
		}
		Dao::commit();
	}

}
