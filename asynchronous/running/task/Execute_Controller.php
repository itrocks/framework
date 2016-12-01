<?php

namespace ITRocks\Framework\Asynchronous\Running\Task;

use ITRocks\Framework\Asynchronous\Running;
use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 */
class Execute_Controller implements Feature_Controller
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
		/** @var $asynchronous Running\Task */
		$asynchronous = $parameters->getMainObject();
		if ($asynchronous && $asynchronous instanceof Running\Task) {
			if ($asynchronous->canExecute()) {
				$asynchronous->run();
			}
		}
		return 'Executed';
	}

}
