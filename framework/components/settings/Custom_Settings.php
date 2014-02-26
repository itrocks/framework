<?php
namespace SAF\Framework;

/**
 * Custom settings objects can be loaded and saved from user configuration
 */
abstract class Custom_Settings
{

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The name of the class which list settings apply
	 *
	 * @var string
	 */
	public $class_name;

	//----------------------------------------------------------------------------------------- $name
	/**
	 * A readable for the list settings
	 *
	 * @var string
	 */
	public $name;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * @var Setting
	 */
	public $setting;

	//---------------------------------------------------------------------------- currentUserSetting
	/**
	 * @param $class_name string
	 * @return Setting
	 */
	public static function currentUserSetting($class_name)
	{
		$setting = new User_Setting($class_name . '.' . static::customId());
		return Dao::searchOne($setting) ?: $setting;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string class name that identifies setting
	 * @return Custom_Settings
	 */
	public static function current($class_name)
	{
		$setting = self::currentUserSetting($class_name);
		if (isset($setting->value)) {
			$custom_settings = $setting->value;
		}
		else {
			$custom_settings = Builder::create(get_called_class(), [$class_name]);
			$setting->value = $custom_settings;
		}
		$custom_settings->setting = $setting;
		return $custom_settings;
	}

	//-------------------------------------------------------------------------------------- customId
	/**
	 * @return string
	 */
	protected static function customId()
	{
		return lParse(strtolower(Namespaces::shortClassName(get_called_class())), '_');
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete the List_Settings object from the Settings set
	 */
	public function delete()
	{
		if ($this->name) {
			$code = $this->class_name . '.' . static::customId();
			$setting = new Setting($code . '.' . $this->name);
			$setting = Dao::searchOne($setting);
			if (isset($setting)) {
				Dao::delete($setting);
			}
		}
	}

	//----------------------------------------------------------------------------- getCustomSettings
	/**
	 * Gets custom settings list
	 *
	 * @return Custom_Settings[] key is the name of the setting, value is '' or 'selected'
	 */
	public function getCustomSettings()
	{
		$list = [];
		$search['code'] = $this->class_name . '.' . static::customId() . '.%';
		/** @var $setting Setting */
		foreach (Dao::search($search, Setting::class) as $setting) {
			/** @var $settings Custom_Settings */
			$settings = $setting->value;
			if (!is_object($settings)) {
				echo '<b>' . $setting->id . '</b>';
			}
			$list[$settings->name] = (($settings->name == $this->name) ? 'selected' : '');
		}
		ksort($list);
		return $list;
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads a List_Settings from the Settings set
	 *
	 * If no List_Settings named $name is stored, a new one will be returned
	 *
	 * @param $class_name string
	 * @param $name       string
	 * @return Custom_Settings
	 */
	public static function load($class_name, $name)
	{
		$setting = new Setting($class_name . '.' . static::customId() . '.' . $name);
		$setting = Dao::searchOne($setting);
		$custom_settings = isset($setting)
			? $setting->value
			: Builder::create(get_called_class(), [$class_name]);
		$custom_settings->setting = self::currentUserSetting($class_name);
		$custom_settings->setting->value = $custom_settings;
		return $custom_settings;
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * If $save_name is set : saves the List_Settings object into the Settings set
	 * If $save_name is not set : saves the List_Settings object for current user and session
	 *
	 * @param $save_name string
	 */
	public function save($save_name = null)
	{
		if (isset($save_name)) {
			$this->name = $save_name;
			$setting = new Setting($this->class_name . '.' . static::customId() . '.' . $save_name);
			$setting = Dao::searchOne($setting) ?: $setting;
			$setting->value = $this;
			Dao::write($setting);
		}
		elseif ($this) {
			Dao::write($this->setting);
		}
	}

}
