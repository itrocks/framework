<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Trigger\Action\Status;
use ITRocks\Framework\Trigger\Schedule;
use ITRocks\Framework\Trigger\Schedule\Action;
use ITRocks\Framework\Trigger\Server\Manager;
use ITRocks\Framework\View;

/**
 * Scheduled plugins : create an action on install, remove it on uninstall
 *
 * const SCHEDULE_HOUR = '00:00' : every class using this trait should initialize it
 * Every class using this trait should
 */
trait Scheduled
{

	//------------------------------------------------------------------------------------- actionUri
	/**
	 * @return string
	 */
	protected function actionUri() : string
	{
		return View::link(static::class, 'scheduledRun');
	}

	//--------------------------------------------------------------------------------------- install
	public function install() : void
	{
		Dao::write($this->schedule());
		(new Manager)->activate();
	}

	//-------------------------------------------------------------------------------------- schedule
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Schedule
	 */
	protected function schedule() : Schedule
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$schedule          = Builder::create(Schedule::class);
		$schedule->hours   = static::SCHEDULE_HOUR;
		$schedule->name    = $this->scheduleName();
		$schedule->actions = [$this->scheduleAction()];
		return $schedule;
	}

	//-------------------------------------------------------------------------------- scheduleAction
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Action
	 */
	protected function scheduleAction() : Action
	{
		/** @noinspection PhpUnhandledExceptionInspection class */
		$action         = Builder::create(Action::class);
		$action->action = $this->actionUri();
		$action->status = Status::STATIC;
		return $action;
	}

	//---------------------------------------------------------------------------------- scheduleName
	protected function scheduleName() : string
	{
		return Loc::tr('Every day at :hourAM', Loc::replace(['hour' => static::SCHEDULE_HOUR]));
	}

	//------------------------------------------------------------------------------------- uninstall
	public function uninstall() : void
	{
		$search = ['action' => $this->actionUri(), 'status' => Status::STATIC];
		/** @var $action Action */
		foreach (Dao::search($search, Action::class) as $action) {
			foreach (Dao::search(['parent' => $action], Action::class) as $executed_action) {
				$executed_action->parent = null;
				Dao::write($executed_action, Dao::only('parent'));
			}
			/** @var $schedule Schedule */
			foreach (Dao::search(['actions' => $action], Schedule::class) as $schedule) {
				if (count($schedule->actions) === 1) {
					Dao::delete($schedule);
				}
			}
			Dao::delete($action);
		}
	}

}
