<?php
namespace SAF\Framework;

// TODO doc : what will a feature controller be ?
interface Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 */
	public function run(Controller_Parameters $parameters, $form, $files);

}
