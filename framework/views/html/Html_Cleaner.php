<?php
namespace SAF\Framework;

use SAF\Plugins;

/**
 * This plugin cleans HTML code to avoid multiple blank lines, etc.
 */
class Html_Cleaner implements Plugins\Registerable
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
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod(
			array('SAF\Framework\Html_Template', "parse"), array($this, "clean")
		);
	}

}
