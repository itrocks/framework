<?php
namespace ITRocks\Framework\Controller;

use ITRocks\Framework\Controller;

/**
 * A feature controller is a single controller class devoted to one class and one feature
 */
interface Feature_Controller extends Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string;

}
