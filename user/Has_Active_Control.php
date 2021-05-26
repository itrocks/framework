<?php
namespace ITRocks\Framework\User;

use ITRocks\Framework\Trigger\Schedule\user_restriction\Action;
use ITRocks\Framework\Updater\Will_Call;
use ITRocks\Framework\User;

/**
 * @extends User
 * @feature Add a control to set a user active or not
 * @feature_include Has_Active
 * @feature_install installBanAction
 * @feature_uninstall uninstallBanAction
 * @see User
 */
trait Has_Active_Control
{

	//------------------------------------------------------------------------------ installBanAction
	/**
	 * @noinspection PhpUnused @feature_install
	 */
	public static function installBanAction()
	{
		Will_Call::add([new Action(), 'execute']);
	}

	//---------------------------------------------------------------------------- uninstallBanAction
	/**
	 * @noinspection PhpUnused @feature_uninstall
	 */
	public static function uninstallBanAction()
	{
		Will_Call::add([new Action(), 'stop']);
	}

}
