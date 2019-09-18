<?php
namespace ITRocks\Framework\User\Password\Reset;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Mapper\Object_Builder_Array;
use ITRocks\Framework\User\Password;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * Password reset controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'reset';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$password = $parameters->getMainObject(Password::class);
		if ($token = $parameters->getRawParameter('token')) {
			$template = $password->apply($token) ? 'done' : 'already';
			$parameters->set(Template::TEMPLATE, $template);
		}
		else {
			(new Object_Builder_Array(Password::class))->build($form, $password);
			if ($password->login && $password->password && $password->password2) {
				$password->reset();
				$parameters->set(Template::TEMPLATE, 'sent');
			}
		}
		return View::run($parameters->getObjects(), $form, $files, Password::class, static::FEATURE);
	}

}
