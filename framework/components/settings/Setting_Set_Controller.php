<?php
namespace SAF\Framework;

/**
 * Setting set controller
 */
class Setting_Set_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		Session::current()->get('SAF\Framework\Settings', true)->add(
			$parameters->getRawParameter("code"),
			$parameters->getRawParameter("value")
		);
	}

}
