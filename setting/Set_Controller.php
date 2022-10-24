<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Session;

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
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		Session::current()->get(Settings::class, true)->add(
			$parameters->getRawParameter('code'),
			$parameters->getRawParameter('value')
		);
		return null;
	}

}
