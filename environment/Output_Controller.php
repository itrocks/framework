<?php
namespace ITRocks\Framework\Environment;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Environment;
use ITRocks\Framework\Session;
use ITRocks\Framework\View;

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
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$environment = Session::current()->get(Environment::class, true);
		$parameters = array_merge([get_class($environment) => $environment], $parameters->getObjects());
		return View::run($parameters, $form, $files, get_class($environment), Feature::F_OUTPUT);
	}

}
