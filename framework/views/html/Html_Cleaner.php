<?php
namespace SAF\Framework;

/**
 * This plugin cleans HTML code to avoid multiple blank lines, etc.
 */
abstract class Html_Cleaner implements Plugin
{

	//----------------------------------------------------------------------------------------- clean
	/**
	 * @param $result string
	 */
	public static function clean(&$result)
	{
		$result = str_replace("\r", "", $result);
		$result = preg_replace("/(\n)([\\s|\\t]+)(\n)/", "\n", $result);
	}

	//-------------------------------------------------------------------------------------- register
	public static function register()
	{
		Aop::addAfterMethodCall(
			array('SAF\Framework\Html_Template', "parse"), array(__CLASS__, "clean")
		);
	}

}
