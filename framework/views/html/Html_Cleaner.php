<?php
namespace SAF\Framework;
use AopJoinPoint;

abstract class Html_Cleaner
{

	//----------------------------------------------------------------------------------------- clean
	/**
	 * @param AopJoinPoint $joinpoint
	 */
	public static function clean(AopJoinPoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$content = str_replace("\r", "", $content);
		$content = preg_replace("/(\n)([\s|\t]+)(\n)/","\n", $content);
		$joinpoint->setReturnedValue($content);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::registerAfter(__NAMESPACE__ . "\\Html_Template->parse()", array(__CLASS__, "clean"));
	}

}

Html_Cleaner::register();
