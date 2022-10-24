<?php
namespace ITRocks\Framework\User\Password;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\User;
use ITRocks\Framework\View;

/**
 * Password view
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'password';

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$user           = $parameters->getMainObject(User::class);
		$action_object  = $parameters->shiftNamed();
		$action_feature = $parameters->shiftUnnamed();
		$parameters     = $parameters->getObjects();

		$parameters['action'] = View::link($action_object, $action_feature);

		if (empty($parameters['message'])) {
			$parameters['message'] = 'Please enter your password';
		}
		if (empty($parameters['submit'])) {
			$parameters['submit'] = 'Submit';
		}
		if (!isset($parameters['target'])) {
			$parameters['target'] = Target::MAIN;
		}

		return View::run($parameters, $form, $files, get_class($user), static::FEATURE);
	}

}
