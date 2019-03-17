<?php
namespace ITRocks\Framework\View\Html;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

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
		$result = preg_replace('~\n\s+\n~', LF, $result);
		$result = preg_replace('~</nav>\s+<main~', '</nav><main', $result);
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
