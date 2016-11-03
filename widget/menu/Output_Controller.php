<?php
namespace ITRocks\Framework\Widget\Menu;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;
use ITRocks\Framework\View;
use ITRocks\Framework\Widget\Menu;

/**
 * The Menu output controller outputs the current menu if none is passed as parameter
 */
class Output_Controller implements Feature_Controller
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
		$parameters = $parameters->getObjects();
		if (!(reset($parameters)) instanceof Menu) {
			array_unshift($parameters, Session::current()->plugins->get(Menu::class));
		}
		return View::run($parameters, $form, $files, Menu::class, Feature::F_OUTPUT);
	}

}
