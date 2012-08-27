<?php

class Test_Order_Output_Controller implements Controller
{

	//------------------------------------------------------------------------------------------ call
	/**
	 * @param array $args
	 */
	public function call($params, $form, $files)
	{
		$params = Main_Controller::getParameters($params);
		echo "called !<br>";
		echo "<pre>" . print_r($params, true) . "</pre>";
	}
	
}
