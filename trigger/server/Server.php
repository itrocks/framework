<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Logger\Entry\Data;
use ITRocks\Framework\Tools\Asynchronous;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\User\Authenticate\By_Token;

/**
 * Trigger asynchronous execution server
 */
class Server
{

	//------------------------------------------------------------------------------------------ STOP
	/**
	 * The specific command STOP can be set as an action to stop the trigger server
	 */
	const STOP = 'STOP';

	//--------------------------------------------------------------------------------- $asynchronous
	/**
	 * @var Asynchronous
	 */
	public $asynchronous;

	//----------------------------------------------------------------------------------------- $stop
	/**
	 * If true, then server will stop
	 *
	 * @var boolean
	 */
	public $stop = false;

	//----------------------------------------------------------------------------------- afterAction
	/**
	 * @param $action Action
	 */
	public function afterAction(Action $action)
	{
		$this->launchedActionStatus($action, true);
	}

	//---------------------------------------------------------------------------------- launchAction
	/**
	 * @param $action Action
	 * @param $last   Date_Time
	 */
	protected function launchAction(Action $action, Date_Time $last)
	{
		Dao::begin();
		$action->status = Action\Status::LAUNCHING;
		Dao::write($action, Dao::only('status'));
		$action->next($last, true);
		Dao::commit();
		$uri = $action->action;
		if ($action->as_user) {
			$token = (new By_Token)->newToken($action->as_user);
			$uri  .= (strpos($uri, '?') ? '&' : '?') . By_Token::TOKEN . '=' . $token;
		}
		/** @var $callback callable */
		$callback       = [$this, 'afterAction'];
		$callback[]     = Dao::getObjectIdentifier($action) ? $action : null;
		$process        = $this->asynchronous->call($uri, $callback, false, true);
		$action->status = ($process->identifier && $process->unique_identifier)
			? Action\Status::LAUNCHED
			: Action\Status::LAUNCH_ERROR;
		$action->request_identifier = $process->unique_identifier;
		Dao::write($action, Dao::only('request_identifier', 'status'));
	}

	//-------------------------------------------------------------------------- launchedActionStatus
	/**
	 * Calculate the status of an action that is launched or running
	 *
	 * @param $action       Action
	 * @param $process_done boolean if true, the process is done and an empty stop date-time is error
	 */
	protected function launchedActionStatus(Action $action, $process_done = false)
	{
		$data = Dao::searchOne(['request_identifier' => $action->request_identifier], Data::class);
		if ($data) {
			$action->status = $data->entry->stop->isEmpty()
				? ($process_done ? Action\Status::ERROR : Action\Status::RUNNING)
				: Action\Status::DONE;
			Dao::write($action, Dao::only('status'));
		}
	}

	//------------------------------------------------------------------------------------------ loop
	/**
	 * Server loop iteration : list, execute and calculate next execution time of actions
	 *
	 * @return integer count executed actions
	 */
	public function loop()
	{
		// next scheduled actions
		$actions = Dao::search(
			[
				'next'   => Func::lessOrEqual(Date_Time::now()),
				'status' => [Action\Status::LAUNCHED, Action\Status::PENDING, Action\Status::STATIC]
			],
			Action::class
		);
		$last = Date_Time::now();
		foreach ($actions as $action) {
			if ($action->action === static::STOP) {
				$this->stopAction($action, $last);
			}
			if ($this->stop) {
				continue;
			}
			if ($action->status === Action\Status::LAUNCHED) {
				$this->launchedActionStatus($action);
			}
			elseif ($action->status === Action\Status::PENDING) {
				$this->launchAction($action, $last);
			}
			elseif ($action->status === Action\Status::STATIC) {
				if ($action = $action->execute()) {
					$this->launchAction($action, $last);
				}
			}
		}
		return count($actions);
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * Runs the server : will not stop before asked using a 'STOP' action
	 */
	public function run()
	{
		$this->asynchronous = new Asynchronous();
		while (!$this->stop) {
			$next_execution = floatval(floor(microtime(true) + 1));
			if (!$this->loop()) {
				$sleep_duration = max(0, ceil(($next_execution - microtime(true)) * 1000000));
				usleep($sleep_duration);
			}
			$this->asynchronous->flush();
		}
		$this->asynchronous->wait();
	}

	//------------------------------------------------------------------------------------ stopAction
	/**
	 * @param $action Action
	 * @param $last   Date_Time
	 */
	public function stopAction(Action $action, Date_Time $last)
	{
		$this->stop     = true;
		$action->last   = $last;
		$action->status = Action\Status::DONE;
		Dao::write($action, Dao::only('last', 'status'));
	}

}
