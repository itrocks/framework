<?php
namespace SAF\Framework;

/** @noinspection PhpIncludeInspection */
require_once "framework/core/toolbox/Aop.php";

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
