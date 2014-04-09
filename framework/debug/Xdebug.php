<?php
namespace SAF\Framework\Debug;

use SAF\Framework\Controller\Main;
use SAF\Plugins;

/**
 * The Xdebug plugin disable XDEBUG_SESSION_START and KEY get vars to avoid side effects
 */
class Xdebug implements Plugins\Registerable
{

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * @param $get string[]
	 */
	public static function cleanup(&$get)
	{
		unset($get['XDEBUG_PROFILE']);
		if (isset($get['XDEBUG_SESSION_START'])) {
			unset($get['XDEBUG_SESSION_START']);
			unset($get['KEY']);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param Plugins\Register $register
	 */
	public function register(Plugins\Register $register)
	{
		$aop = $register->aop;
		$aop->beforeMethod([Main::class, 'runController'], [__CLASS__, 'cleanup']);
	}

}
