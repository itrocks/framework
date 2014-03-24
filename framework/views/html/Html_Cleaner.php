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
		$result = str_replace(CR, '', $result);
		$result = preg_replace('/(\n)([\\s|\\t]+)(\n)/', LF, $result);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Plugins\Register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Html_Template::class, 'parse'], [$this, 'clean']);
	}

}
