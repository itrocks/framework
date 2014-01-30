<?php
namespace SAF\Framework;

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
class Xdebug implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	/**
	 *
	 * @param $register Plugin_Register
	*/
	public function register(Plugin_Register $register)
	{
		if (isset($_GET["XDEBUG_SESSION_START"]) && isset($_GET["KEY"])) {
			unset($_GET["XDEBUG_SESSION_START"]);
			unset($_GET["KEY"]);
		}
	}

}
