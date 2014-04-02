<?php
namespace SAF\Framework;

/**
 * For classes that need the main controller object to run
 * Can be used by :
 * - Updatable
 */
interface Needs_Main_Controller
{

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main_Controller
	 */
	public function setMainController(Main_Controller $main_controller);

}
