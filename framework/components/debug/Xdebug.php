<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
class Xdebug implements Plugins\Activable
{

	//-------------------------------------------------------------------------------------- activate
	public function activate()
	{
		if (isset($_GET["XDEBUG_SESSION_START"]) && isset($_GET["KEY"])) {
			unset($_GET["XDEBUG_SESSION_START"]);
			unset($_GET["KEY"]);
		}
	}

}
