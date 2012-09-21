<?php
namespace SAF\Framework;

require_once "framework/classes/toolbox/Aop.php";

abstract class Xdebug
{

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::registerBefore(
			__NAMESPACE__ . "\\Main_Controller->dispatchParams()",
			__NAMESPACE__ . "\\Xdebug::removeXdebugParams"
		);
	}

	//---------------------------------------------------------------------------- removeXdebugParams
	/**
	 * Remove Xdebug params
	 *
	 * @param AopJoinPoint $joinpoint
	 */
	public static function removeXdebugParams(AopJoinPoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		if (isset($arguments[0]["XDEBUG_SESSION_START"]) && is_numeric($arguments[0]["KEY"])) {
			unset($arguments[0]["XDEBUG_SESSION_START"]);
			unset($arguments[0]["KEY"]);
		}
	}

}

Xdebug::register();
