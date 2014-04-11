<?php
namespace SAF\Framework\Setting;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Session;

/**
 * Setting set controller
 */
class Set_Controller implements Feature_Controller
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
		Session::current()->get(Settings::class, true)->add(
			$parameters->getRawParameter('code'),
			$parameters->getRawParameter('value')
		);
	}

}
