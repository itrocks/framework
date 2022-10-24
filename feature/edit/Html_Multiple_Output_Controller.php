<?php
namespace ITRocks\Framework\Feature\Edit;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;

/**
 * Html edit multiple controller
 */
class Html_Multiple_Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Run method for the class controller, when no runFeatureName() method was found in it.
	 *
	 * Class controllers must implement this method if you want the controller to work.
	 *
	 * @param $parameters   Parameters
	 * @param $form         array
	 * @param $files        array[]
	 * @return string
	 */
	public function run(Parameters $parameters, array $form, array $files) : string
	{
		return 'check this out man'
			. PRE . print_r($parameters->getObjects(), true) . _PRE;

	}

}
