<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Paths;
use ITRocks\Framework\User\Authenticate\Authentication;

/**
 * Disconnects current user
 *
 * Do not call this if there is no current user.
 */
class Disconnect_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		Authentication::disconnect();
		Session::current()->stop();
		return '<script> location = ' . Q . Paths::$uri_base . Q . '; </script>';
	}

}
