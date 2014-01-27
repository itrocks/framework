<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/aop/Aop.php";

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
abstract class Xdebug implements Plugin
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		if (isset($_GET["XDEBUG_SESSION_START"]) && isset($_GET["KEY"])) {
			unset($_GET["XDEBUG_SESSION_START"]);
			unset($_GET["KEY"]);
		}
	}

}
