<?php
namespace SAF\Framework;

/**
 * Disconnects current user
 *
 * Do not call this if there is no current user.
 */
class User_Disconnect_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array                 $form
	 * @param array                 $files
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		$current_user = User::current();
		if (!isset($current_user)) {
			$current_user = new User();
		}
		User_Authentication::disconnect($current_user);
		array_unshift($parameters, $current_user);
		session_destroy();
		return View::run($parameters, $form, $files, get_class($current_user), 'disconnect');
	}

}
