<?php
namespace SAF\Framework;

class Application_Home_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		return View::run($parameters->getObjects(), $form, $files, "Application", "home");
	}

}
