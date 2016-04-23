<?php
namespace SAF\Framework\Environment;

use SAF\Framework\Controller\Feature;
use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Environment;
use SAF\Framework\Session;
use SAF\Framework\View;

/**
 * Environment output controller
 */
class Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		$environment = Session::current()->get(Environment::class, true);
		$parameters = array_merge([get_class($environment) => $environment], $parameters->getObjects());
		return View::run($parameters, $form, $files, get_class($environment), Feature::F_OUTPUT);
	}

}
