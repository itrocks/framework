<?php
namespace SAF\Framework;

/**
 * Custom settings objects can be loaded and saved from user configuration
 */
trait Custom_Settings
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

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string
	 * @return Custom_Settings
	 */
	public static function current($class_name)
	{
		/** @var $settings Settings */
		$settings = Settings::ofCurrentSession();
		/** @var $setting Setting */
		$setting = $settings->get($class_name . "." . static::customId());
		if (!isset($setting)) {
			$custom_settings = Builder::create(get_called_class(), array($class_name));
			$custom_settings->setting = $settings->add(
				new User_Setting($class_name . "." . static::customId(), $custom_settings)
			);
		}
		else {
			$custom_settings = $setting->value;
			$custom_settings->setting = $setting;
		}
		return $custom_settings;
	}

	//-------------------------------------------------------------------------------------- customId
	/**
	 * @return string
	 */
	protected static function customId()
	{
		return lParse(strtolower(Namespaces::shortClassName(get_called_class())), "_");
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete the List_Settings object from the Settings set
	 */
	public function delete()
	{
		if ($this->name) {
			$code = $this->class_name . "." . static::customId();
			$setting = Search_Object::create('SAF\Framework\Setting');
			$setting->code = $code . "." . $this->name;
			$setting = Dao::searchOne($setting);
			if (isset($setting)) {
				Dao::delete($setting);
			}
		}
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
		$code = $class_name . "." . static::customId();
		$setting = Search_Object::create('SAF\Framework\Setting');
		$setting->code = $code . "." . $name;
		$setting = Dao::searchOne($setting);
		$custom_settings = (isset($setting))
			? unserialize($setting->value)
			: Builder::create($class_name);
		$custom_settings->setting = Settings::ofCurrentSession()->get($code);
		$custom_settings->setting->value = $custom_settings;
		$custom_settings->save();
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
			$setting = new Setting($this->class_name . "." . static::customId() . "." . $save_name);
			$setting = Dao::searchOne($setting) ?: $setting;
			$setting->value = $this;
			Dao::write($setting);
		}
		elseif ($this->setting) {
			Dao::write($this->setting);
		}
	}

}
