<?php
namespace SAF\Framework\Main;

/**
 * Application home page view controller
 */
class Application_Home_Controller implements Feature_Controller
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
			$parameters->getObjects(), $form, $files, Namespaces::fullClassName('Application'), 'home'
		);
	}

}
