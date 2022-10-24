<?php
namespace ITRocks\Framework\Component\Menu;

use ITRocks\Framework\Component\Menu;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\View;

/**
 * The Menu output controller outputs the current menu if none is passed as parameter
 */
class Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		$parameters = $parameters->getObjects();
		if (!(reset($parameters)) instanceof Menu) {
			array_unshift($parameters, Menu::get());
		}
		return View::run($parameters, $form, $files, Menu::class, Feature::F_OUTPUT);
	}

}
