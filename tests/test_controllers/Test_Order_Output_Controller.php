<?php
namespace SAF\Framework\Tests;
use SAF\Framework\Controller_Parameters;
use SAF\Framework\Feature_Controller;

class Test_Order_Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array $form
	 * @param array $files
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		echo "<pre>test order output controller " . print_r($parameters, true) . "</pre>";
	}
	
}
