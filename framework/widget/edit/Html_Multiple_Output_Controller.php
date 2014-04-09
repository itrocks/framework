<?php
namespace SAF\Framework\Widget\Edit;

use SAF\Framework\Controller\Feature_Controller;
use SAF\Framework\Controller\Parameters;

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
	 * @param $files        array
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files)
	{
		echo 'check this out man';
		echo '<pre>' . print_r($parameters->getObjects(), true) . '</pre>';
	}

}
