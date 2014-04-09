<?php
namespace SAF\Framework\Plugin;

use SAF\Framework\Plugin;

/**
 * Activable plugins are registered on session start, and activated each time the class is loaded
 */
interface Activable extends Plugin
{

	//-------------------------------------------------------------------------------------- activate
	public function activate();

}
