<?php
namespace SAF\Framework;

// TODO doc : what will a feature controller be ?
interface Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Controller_Parameters
	 * @param $form array
	 * @param $files array
	 */
	public function run(Controller_Parameters $parameters, $form, $files);

}
