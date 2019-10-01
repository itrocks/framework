<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\View;

/**
 * Disconnects current user
 *
 * Do not call this if there is no current user.
 */
class Disconnect_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$parameters   = $parameters->getObjects();
		$current_user = User::current();
		if (!isset($current_user)) {
			/** @noinspection PhpUnhandledExceptionInspection class */
			$current_user = Builder::create(User::class);
		}
		Authentication::disconnect($current_user);
		array_unshift($parameters, $current_user);
		Session::current()->stop();
		return View::run($parameters, $form, $files, get_class($current_user), 'disconnect');
	}

}
