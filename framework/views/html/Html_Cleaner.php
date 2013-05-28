<?php
namespace SAF\Framework;
use AopJoinpoint;

/**
 * This plugin cleans HTML code to avoid multiple blank lines, etc.
 */
abstract class Html_Cleaner implements Plugin
{

	//----------------------------------------------------------------------------------------- clean
	/**
	 * @param $joinpoint AopJoinpoint
	 */
	public static function clean(AopJoinpoint $joinpoint)
	{
		$content = $joinpoint->getReturnedValue();
		$content = str_replace("\r", "", $content);
		$content = preg_replace("/(\n)([\\s|\\t]+)(\n)/", "\n", $content);
		$joinpoint->setReturnedValue($content);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::add(Aop::AFTER, 'SAF\Framework\Html_Template->parse()', array(__CLASS__, "clean"));
	}

}
