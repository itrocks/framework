<?php
namespace ITRocks\Framework\Trigger\Server;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Trigger\Server;

/**
 * Trigger server run controller
 */
class Run_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'run';

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
		(new Server)->run();
		return 'OK';
	}

}
