<?php
namespace SAF\Framework;

/**
 * Set of settings
 *
 * @override elements @link Collection
 * @override elements @type Setting[]
 */
class Settings extends Set
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $elements object[] Setting[]
	 */
	public function __construct($elements = null)
	{
		$settings = array();
		if (isset($elements)) {
			foreach ($elements as $setting) {
				$settings[$setting->code] = $setting;
			}
		}
		parent::__construct(Builder::className('SAF\Framework\Setting'), $settings);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Sets a value for a given setting
	 *
	 * @param $code  string|integer|Setting
	 * @param $value string|Setting
	 * @return Setting
	 */
	public function add($code, $value = null)
	{
		if ($code instanceof Setting) {
			parent::add($code->code, $code);
			$setting = $code;
		}
		elseif ($value instanceof Setting) {
			parent::add($code, $value);
			$setting = $value;
		}
		else {
			$setting = $this->get($code);
			if (isset($setting)) {
				$setting->value = $value;
			}
			else {
				parent::add($code, $setting = new Setting($code, $value));
			}
		}
		return $setting;
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @param string $code
	 * @return Setting
	 */
	public function get($code)
	{
		/** @var $setting Setting */
		$setting = parent::get($code);
		if (
			isset($setting)
			&& is_string($setting->value)
			&& (substr($setting->value, 0, 2) == "O:")
			&& substr($setting->value, -1) === "}"
		) {
			$setting->value = unserialize($setting->value);
		}
		return $setting;
	}

	//------------------------------------------------------------------------------ ofCurrentSession
	/**
	 * @return Settings
	 */
	public static function ofCurrentSession()
	{
		$settings = Session::current()->get('SAF\Framework\Settings');
		if (!isset($settings)) {
			$settings = self::ofCurrentUser();
			Session::current()->set($settings);
		}
		return $settings;
	}

	//--------------------------------------------------------------------------------- ofCurrentUser
	/**
	 * Get settings of the currently connected user
	 *
	 * @return Settings
	 */
	public static function ofCurrentUser()
	{
		/** @var $user User|User_Has_Settings*/
		$user = User::current();
		return new Settings($user->settings);
	}

}
