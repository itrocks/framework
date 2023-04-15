<?php
namespace ITRocks\Framework\Application\Home;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Application home page view controller
 */
class Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/** @param $files array[] */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$parameters->unshift(Application::current());
		return View::run(
			$parameters->getObjects(), $form, $files, get_class(Application::current()), 'home'
		);
	}

}
