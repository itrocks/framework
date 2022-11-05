<?php
namespace ITRocks\Framework\Plugin;

use ITRocks\Framework\Plugin;

/**
 * Activable plugins are registered on session start, and activated each time the class is loaded
 */
interface Activable extends Plugin
{

	//-------------------------------------------------------------------------------------- activate
	/**
	 * This method is called each time the class is loaded
	 * = when you need the plugin for the first time during the script execution
	 */
	public function activate() : void;

}
