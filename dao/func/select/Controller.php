<?php
namespace ITRocks\Framework\Dao\Func\Select;

use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\View;

/**
 * Func select controller
 */
class Controller implements Feature_Controller
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
		$parameters->getMainObject(Func_Select::class);
		return View::run($parameters->getObjects(), $form, $files, Func::class, Feature::F_SELECT);
	}

}
