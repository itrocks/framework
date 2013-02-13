<?php
namespace SAF\Framework;

class Menu_Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		array_unshift($parameters, Menu::current());
		return View::run($parameters, $form, $files, "Menu", "output");
	}

}
