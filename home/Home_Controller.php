<?php
namespace ITRocks\Framework\Home;

use ITRocks\Framework\Application;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * Application home page view controller
 */
class Home_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$parameters->unshift(Application::current());
		return View::run(
			$parameters->getObjects(), $form, $files, get_class(Application::current()), 'home'
		);
	}

}
