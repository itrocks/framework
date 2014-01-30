<?php
namespace SAF\Framework;

/**
 * Activable plugins are registered on session start, and activated each time the class is loaded
 */
interface Activable_Plugin extends Plugin
{

	//-------------------------------------------------------------------------------------- activate
	public function activate();

}
