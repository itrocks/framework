<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/classes/toolbox/Aop.php";

abstract class Xdebug
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
