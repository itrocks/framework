<?php
namespace SAF\Framework;
use AopJoinPoint;

abstract class Aop_Logger
{

	//------------------------------------------------------------------------------------------- log
	/**
	 * @param AopJoinPoint $joinpoint
	 */
	public static function log(AopJoinPoint $joinpoint)
	{
		$arguments = $joinpoint->getArguments();
		echo "<div class=\"Aop logger " . $joinpoint->getMethodName() . "\">"
			. "<b>" . $joinpoint->getMethodName() . "</b> "
			. print_r($arguments[0], true) . " -&gt; " . print_r($arguments[1], true)
			. "</div>";
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::registerBefore(
			__NAMESPACE__ . "\\Aop->registerAfter()",   __NAMESPACE__ . "\\Aop_Logger::log"
		);
		Aop::registerBefore(
			__NAMESPACE__ . "\\Aop->registerArround()", __NAMESPACE__ . "\\Aop_Logger::log"
		);
		Aop::registerBefore(
			__NAMESPACE__ . "\\Aop->registerBefore()",  __NAMESPACE__ . "\\Aop_Logger::log"
		);
	}

}

Aop_Logger::register();
