<?php
namespace SAF\Framework;

class Property_Add_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * Add property controller
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		// the default property add controller does... nothing
		return "<pre>" . print_r($parameters, true) . "</pre>";
	}

}
