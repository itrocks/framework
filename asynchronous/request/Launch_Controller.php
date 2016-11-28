<?php

namespace ITRocks\Framework\Asynchronous\Request;

use ITRocks\Framework\Asynchronous\Request;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 */
class Launch_Controller implements Feature_Controller
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
		/** @var $asynchronous Request */
		$asynchronous = $parameters->getMainObject();
		if ($asynchronous && $asynchronous instanceof Request) {
			// TODO : check if previous process is not in progress
			$asynchronous->asynchronousLaunch();
		}
		return (new List_Controller())->run($parameters, $form, $files, get_class($asynchronous));
	}

}
