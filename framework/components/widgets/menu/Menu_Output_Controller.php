<?php
namespace SAF\Framework;

/**
 * The Menu output controller outputs the current menu if none is passed as parameter
 */
class Menu_Output_Controller implements Feature_Controller
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
		$parameters = $parameters->getObjects();
		if (!(reset($parameters)) instanceof Menu) {
			array_unshift($parameters, Session::current()->getPlugin('SAF\Framework\Menu'));
		}
		return View::run($parameters, $form, $files, "Menu", "output");
	}

}
