<?php
namespace ITRocks\Framework\Trigger;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Tools\Asynchronous;
use ITRocks\Framework\Tools\Date_Time;

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
	public function afterAction(Action $action = null)
	{
		if ($action) {
			$action->running = false;
			Dao::write($action, Dao::only('running'));
		}
	}

	//------------------------------------------------------------------------------------------ loop
	/**
	 * Server loop iteration : list, execute and calculate next execution time of actions
	 */
	public function loop()
	{
		// next scheduled actions
		$actions = Dao::search(
			['next' => Func::lessOrEqual(Date_Time::now()), 'running' => false],
			Action::class
		);
		$last = Date_Time::now();
		foreach ($actions as $action) {
			Dao::begin();
			$action->running = true;
			Dao::write($action, Dao::only('running'));
			$action->next($last);
			Dao::commit();
			if ($action->action !== static::STOP) {
				/** @var $callback callable */
				$callback = [$this, 'afterAction'];
				if (Dao::getObjectIdentifier($action)) {
					$callback[] = $action;
				}
				$this->asynchronous->call($action->action, $callback, false);
			}
			if ($action->action === static::STOP) {
				$this->stop = true;
				break;
			}
		}
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
			$this->loop();
			$sleep_duration = max(0, ceil(($next_execution - microtime(true)) * 1000000));
			usleep($sleep_duration);
			$this->asynchronous->flush();
		}
		$this->asynchronous->wait();
	}

}
