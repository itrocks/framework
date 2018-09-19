<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;

/**
 * Change trigger run controller
 *
 * - List change runs in step 'AFTER'
 * - Qualify them
 * - Run qualified runs
 */
class Run_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		foreach (Dao::search(['step' => Run::AFTER], Run::class) as $run) {
			/** @var $run Run */
			// if after conditions are verified : execute change trigger run actions
			if ($run->change->verifyConditions($run->object, $run->change->after_condition)) {
				Dao::begin();
				$run->step = Run::RUNNING;
				Dao::write($run, Dao::only('step'));
				$run->change->executeActions($run->object);
				Dao::commit();
			}
			// conditions do not match : cancel run
			else {
				Dao::delete($run);
			}
		}
		return 'OK';
	}

}
