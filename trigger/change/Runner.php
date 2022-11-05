<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Action;

/**
 * Change trigger runner
 */
class Runner
{

	//--------------------------------------------------------------------------- completeRunningRuns
	/**
	 * Look at logs to know if the 'running' runs are complete. Mark them as 'complete'.
	 */
	public function completeRunningRuns() : void
	{
		foreach (
			Dao::search(['step' => [Run::PARTIAL, Run::PENDING, Run::RUNNING]], Run::class) as $run
		) {
			$done_actions = 0;
			$search       = ['start' => Func::greaterOrEqual($run->last_update)];
			foreach ($run->actions as $action) {
				$search['uri'][]                     = $action->action;
				$search['data.request_identifier'][] = $action->request_identifier;
				if (in_array(
					$action->status,
					[Action\Status::DONE, Action\Status::ERROR, Action\Status::LAUNCH_ERROR]
				)) {
					$done_actions ++;
				}
			}
			if ($done_actions === count($run->actions)) {
				$run->step = Run::COMPLETE;
				Dao::write($run, Dao::only('step'));
				return;
			}
			/** @var $entries Entry[] */
			$entries      = Dao::search($search, Entry::class);
			$done_entries = 0;
			foreach ($entries as $entry) {
				if (!$entry->stop->isEmpty()) {
					$done_entries ++;
				}
			}
			switch ($done_entries) {
				case 0:
					$run->step = Run::RUNNING;
					break;
				case count($run->actions):
					$run->step = Run::COMPLETE;
					break;
				default:
					$run->step = Run::PARTIAL;
			}
			Dao::write($run, Dao::only('step'));
		}
	}

	//----------------------------------------------------------------------------- purgeCompleteRuns
	/**
	 * Purge runs that are complete since more than 1 day
	 *
	 * Runs are kept 1 day for debugging purpose. This may change if needed
	 */
	public function purgeCompleteRuns() : void
	{
		$search = [
			'last_update' => Func::less(Date_Time::now()->sub(1)),
			'step'        => Run::COMPLETE
		];
		foreach (Dao::search($search, Run::class) as $run) {
			Dao::delete($run);
		}
	}

	//------------------------------------------------------------------------------ qualifyAfterRuns
	/**
	 * Qualify actions that are on 'after' step.
	 *
	 * If conditions are verified, write actions for execution and mark runs as 'pending'
	 */
	public function qualifyAfterRuns() : void
	{
		foreach (Dao::search(['step' => Run::AFTER], Run::class) as $run) {
			// if after conditions are verified : execute change trigger run actions
			if ($run->change->verifyConditions($run->object, $run->change->after_condition)) {
				Dao::begin();
				$run->step = Run::PENDING;
				Dao::write($run, Dao::only('step'));
				$run->actions = $run->change->executeActions($run->object);
				Dao::write($run, Dao::only('actions'));
				Dao::commit();
			}
			// conditions do not match : cancel run
			else {
				Dao::delete($run);
			}
		}
	}

}
