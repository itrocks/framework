<?php
namespace ITRocks\Framework\RAD\Feature;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\RAD\Feature;
use ITRocks\Framework\View;

/**
 * User end-feature uninstall controller
 */
class Uninstall_Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'uninstall';

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		/** @var $feature Feature */
		//$feature = $parameters->getMainObject();
		//$feature->uninstall();
		return View::run($parameters->getObjects(), $form, $files, Feature::class, static::FEATURE);
	}

}
