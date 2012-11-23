<?php
namespace SAF\Framework;

class Trashcan_Drop_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		echo "<pre>drop " . print_r($parameters, true) . "</pre>";
	}

}
