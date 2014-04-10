<?php
namespace SAF\Framework\Widget\Menu;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;
use SAF\Framework\View;
use SAF\Framework\Widget\Menu;

/**
 * The Menu output controller outputs the current menu if none is passed as parameter
 */
class Menu_Output_Controller implements Feature_Controller
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
		return View::run($parameters, $form, $files, 'Menu', 'output');
	}

}
