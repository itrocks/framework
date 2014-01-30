<?php
namespace SAF\Framework;

/**
 * This plugin cleans HTML code to avoid multiple blank lines, etc.
 */
class Html_Cleaner implements Plugin
{

	//----------------------------------------------------------------------------------------- clean
	/**
	 * @param $result string
	 */
	public function clean(&$result)
	{
		$result = str_replace("\r", "", $result);
		$result = preg_replace("/(\n)([\\s|\\t]+)(\n)/", "\n", $result);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $dealer     Aop_Dealer
	 * @param $parameters array
	 */
	public function register($dealer, $parameters)
	{
		$dealer->afterMethodCall(
			array('SAF\Framework\Html_Template', "parse"), array($this, "clean")
		);
	}

}
