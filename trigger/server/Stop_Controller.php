<?php
namespace ITRocks\Framework\Trigger\Server;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Trigger\Action;
use ITRocks\Framework\Trigger\Server;

/**
 * Trigger server stop controller
 */
class Stop_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'stop';

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
		Dao::write(new Action(Server::STOP, Date_Time::now()));
		return 'OK';
	}

}
