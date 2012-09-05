<?php
namespace SAF\Framework\Tests;
use SAF\Framework\feature_Controller;

class Test_Order_Output_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param Controller_Parameters $parameters
	 * @param array $form
	 * @param array $files
	 */
		public function run($parameters, $form, $files)
	{
		$parameters = $parameters->getObjects();
		echo "<pre>test order output controller " . print_r($parameters, true) . "</pre>";
	}
	
}
