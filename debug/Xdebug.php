<?php
namespace ITRocks\Framework\Debug;

use ITRocks\Framework\Controller\Main;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
class Xdebug implements Registerable
{

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $get string[]
	 */
	public static function cleanup(array &$get)
	{
		unset($get['XDEBUG_PROFILE']);
		if (isset($get['XDEBUG_SESSION_START'])) {
			unset($get['XDEBUG_SESSION_START']);
			unset($get['KEY']);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param Register $register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [__CLASS__, 'cleanup']);
	}

}
