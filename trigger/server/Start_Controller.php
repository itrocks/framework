<?php
namespace ITRocks\Framework\Trigger\Server;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Tools\Asynchronous;
use ITRocks\Framework\Trigger\Server;
use ITRocks\Framework\View;

/**
 * Trigger server start controller
 *
 * Starts the server, asynchronously, and give the hand back to the launcher
 */
class Start_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'start';

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
		(new Asynchronous)->call(View::link(Server::class, Run_Controller::FEATURE), null, false, false);
		return 'OK';
	}

}
