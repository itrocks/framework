<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Logger\Entry;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View;

/**
 * Change trigger runner
 */
class Runner
{

	//--------------------------------------------------------------------------- completeRunningRuns
	/**
	 * Look at logs to know if the 'running' runs are complete. Mark them as 'complete'.
	 */
	public function completeRunningRuns()
	{
		foreach (Dao::search(['step' => Run::RUNNING], Run::class) as $run) {
			$search = [
				'uri'  => View::link($run->object),
				'stop' => Func::greaterOrEqual($run->last_update)
			];
			if (Dao::searchOne($search, Entry::class)) {
				$run->step = Run::COMPLETE;
				Dao::write($run, Dao::only('step'));
			}
		}
	}

	//----------------------------------------------------------------------------- detectRunningRuns
	/**
	 * Look at logs to detect runs that started. Mark them as 'running'.
	 */
	public function detectRunningRuns()
	{
		foreach (Dao::search(['step' => Run::PENDING], Run::class) as $run) {
			$search = [
				'uri'   => View::link($run->object),
				'start' => Func::greaterOrEqual($run->last_update)
			];
			/** @var $entry Entry */
			if ($entry = Dao::searchOne($search, Entry::class)) {
				$run->step = ($entry->stop->isEmpty() ? Run::RUNNING : Run::COMPLETE);
				Dao::write($run, Dao::only('step'));
			}
		}
	}

	//----------------------------------------------------------------------------- purgeCompleteRuns
	/**
	 * Purge runs that are complete since more than 1 day
	 *
	 * Runs are kept 1 day for debugging purpose. This may change if needed
	 */
	public function purgeCompleteRuns()
	{
		$search = [
			'last_update' => Func::less(Date_Time::now()->sub(1, Date_Time::DAY)),
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
	public function qualifyAfterRuns()
	{
		foreach (Dao::search(['step' => Run::AFTER], Run::class) as $run) {
			/** @var $run Run */
			// if after conditions are verified : execute change trigger run actions
			if ($run->change->verifyConditions($run->object, $run->change->after_condition)) {
				Dao::begin();
				$run->step = Run::PENDING;
				Dao::write($run, Dao::only('step'));
				$run->change->executeActions($run->object);
				Dao::commit();
			}
			// conditions do not match : cancel run
			else {
				Dao::delete($run);
			}
		}
	}

}
