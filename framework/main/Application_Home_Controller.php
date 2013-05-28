<?php
namespace SAF\Framework;

/**
 * Application home page view controller
 */
class Application_Home_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		return View::run(
			$parameters->getObjects(), $form, $files, Namespaces::fullClassName("Application"), "home"
		);
	}

}
