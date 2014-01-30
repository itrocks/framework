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
	 * @param $register Plugin_Register
	 */
	public function register(Plugin_Register $register)
	{
		$dealer = $register->dealer;
		$dealer->afterMethodCall(
			array('SAF\Framework\Html_Template', "parse"), array($this, "clean")
		);
	}

}
