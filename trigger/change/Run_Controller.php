<?php
namespace ITRocks\Framework\Trigger\Change;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

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
	 * @return string 'OK'
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		$runner = new Runner();
		$runner->qualifyAfterRuns();
		$runner->completeRunningRuns();
		$runner->purgeCompleteRuns();
		return 'OK';
	}

}
