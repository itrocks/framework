<?php
namespace SAF\Framework;
use AopJoinpoint;

require_once "framework/classes/toolbox/Aop.php";

abstract class Xdebug
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		aop_add_before(
			__NAMESPACE__ . "\\Main_Controller->dispatchParams()",
			array(__CLASS__, "removeXdebugParams")
		);
	}

	//---------------------------------------------------------------------------- removeXdebugParams
	/**
	 * Remove Xdebug params
	 *
	 * @param AopJoinpoint $joinpoint
	 */
	public static function removeXdebugParams(AopJoinpoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments[0]["XDEBUG_SESSION_START"]) && is_numeric($arguments[0]["KEY"])) {
			unset($arguments[0]["XDEBUG_SESSION_START"]);
			unset($arguments[0]["KEY"]);
		}
	}

}
