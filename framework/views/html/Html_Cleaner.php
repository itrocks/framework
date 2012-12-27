<?php
namespace SAF\Framework;
use AopJoinpoint;

abstract class Html_Cleaner implements Plugin
{

	//----------------------------------------------------------------------------------------- clean
	/**
	 * @param AopJoinpoint $joinpoint
	 */
	public static function clean(AopJoinpoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$content = str_replace("\r", "", $content);
		$content = preg_replace("/(\n)([\\s|\\t]+)(\n)/","\n", $content);
		$joinpoint->setReturnedValue($content);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add("after", __NAMESPACE__ . "\\Html_Template->parse()", array(__CLASS__, "clean"));
	}

}
