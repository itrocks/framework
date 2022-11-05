<?php
namespace ITRocks\Framework\Controller;

/**
 * For classes that need the main controller object to run
 * Can be used by :
 * - Updatable
 */
interface Needs_Main
{

	//----------------------------------------------------------------------------- setMainController
	/**
	 * @param $main_controller Main
	 */
	public function setMainController(Main $main_controller) : void;

}
