<?php
namespace SAF\Framework\View\Html;

use SAF\Framework\Plugin\Register;
use SAF\Framework\Plugin\Registerable;

/**
 * This plugin cleans HTML code to avoid multiple blank lines, etc.
 */
class Cleaner implements Registerable
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
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Template::class, 'parse'], [$this, 'clean']);
	}

}
