<?php
namespace SAF\Framework;

/**
 * A feature controller is a single controller class devoted to one class and one feature
 */
interface Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files);

}
