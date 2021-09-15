<?php
namespace ITRocks\Framework\Feature\List_Setting;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\List_Setting\Reset\User_Has;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\User;
use ITRocks\Framework\User\Authenticate\Authentication;
use ITRocks\Framework\User\Last_Connection;

/**
 * The reset plugin does the job
 *
 * @feature User list settings automatic reset
 * @feature_build User + User_Has
 * @feature_include Last_Connection
 * @see Last_Connection, User, User_Has
 */
class Reset implements Registerable
{

	//---------------------------------------------------------------------- Reset list setting rules
	const DAILY   = 'daily';
	const SESSION = 'on_session_opening';

	//-------------------------------------------------------------------------------------- dayReset
	/**
	 * Resets all current user lists settings, if its last connection day is before today,
	 * and only if its list reset setting is 'daily'
	 *
	 * @param $user User|Last_Connection\Has|User_Has
	 */
	public function dayReset(User|Last_Connection\Has|User_Has $user)
	{
		if (
			($user->reset_lists === static::DAILY)
			&& Date_Time::today()->isAfterOrEqual($user->last_connection)
		) {
			$this->reset($user);
		}
	}

	//-------------------------------------------------------------------------------------- register
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Authentication::class, 'authenticate'], [$this, 'sessionReset']
		);
		$register->aop->beforeMethod(
			[Last_Connection::class, 'saveLastConnectionDate'], [$this, 'dayReset']
		);
	}

	//----------------------------------------------------------------------------------------- reset
	/**
	 * Reset all list settings for the current user
	 *
	 * @param $user User|User_Has
	 */
	public function reset(User|User_Has $user)
	{
		Dao::begin();
		/** @var $setting Setting\User */
		foreach (Dao::search(['code' => '%.list', 'user' => $user], Setting\User::class) as $setting) {
			/** @var $list_setting Set */
			$list_setting = $setting->value;
			if ($list_setting->search) {
				$list_setting->name    = Loc::tr(Names::classToDisplays($list_setting->class_name));
				$list_setting->search  = [];
				$list_setting->setting = null;
				$setting->setting      = null;
				Dao::write($setting, Dao::only('setting', 'value'));
			}
		}
		Dao::commit();
	}

	//---------------------------------------------------------------------------------- sessionReset
	/**
	 * Resets all current lists settings, only if its reset settings is 'session'
	 *
	 * @param $user User|User_Has
	 */
	public function sessionReset(User|User_Has $user)
	{
		if ($user->reset_lists === static::SESSION) {
			$this->reset($user);
		}
	}

}
