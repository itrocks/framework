<?php
namespace SAF\Framework\Setting;

use SAF\Framework\Builder;
use SAF\Framework\Dao;
use SAF\Framework\Mapper\Search_Object;
use SAF\Framework\Reflection\Reflection_Class;
use SAF\Framework\Setting;
use SAF\Framework\Tools\Namespaces;
use SAF\Framework\Traits\Has_Name;

/**
 * Custom settings objects can be loaded and saved from user configuration
 */
abstract class Custom_Settings
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The name of the class which list settings apply
	 *
	 * @var string
	 */
	private $class_name;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * @store false Custom_Settings is always saved into a Setting, we must not save it again
	 * @var Setting
	 */
	public $setting;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $class_name string
	 * @param $setting    Setting
	 */
	public function __construct($class_name = null, Setting $setting = null)
	{
		if (isset($class_name)) {
			$this->setClassName($class_name);
		}
		if (isset($setting)) {
			$this->setting = $setting;
		}
	}

	//--------------------------------------------------------------------------------------- cleanup
	/**
	 * This cleanup method is called after loading and getting the current value
	 * in order to avoid crashes when some components of the setting disappeared in the meantime.
	 *
	 * @return integer number of changes made during cleanup : if 0, then cleanup was not necessary
	 */
	public abstract function cleanup();

	//---------------------------------------------------------------------------- currentUserSetting
	/**
	 * @param $class_name string
	 * @param $feature    string
	 * @return Setting
	 */
	public static function currentUserSetting($class_name, $feature = null)
	{
		$class_name = Builder::current()->sourceClassName($class_name);
		$setting = new User_Setting($class_name . DOT . static::customId($feature));
		return Dao::searchOne($setting) ?: $setting;
	}

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @param $class_name string class name that identifies setting
	 * @param $feature    string
	 * @return static
	 */
	public static function current($class_name, $feature = null)
	{
		$class_name = Builder::current()->sourceClassName($class_name);
		$setting = self::currentUserSetting($class_name, $feature);
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
	 * @param $feature string
	 * @return string
	 */
	protected static function customId($feature = null)
	{
		if (!$feature) {
			$feature = lParse(strtolower(Namespaces::shortClassName(get_called_class())), '_settings');
		}
		return $feature;
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete the Custom_Settings object from the Settings set
	 *
	 * @param $feature string
	 */
	public function delete($feature = null)
	{
		if ($this->name) {
			$code = $this->getSourceClassName() . DOT . static::customId($feature);
			$setting = new Setting($code . DOT . $this->name);
			$setting = Dao::searchOne($setting);
			if (isset($setting)) {
				Dao::delete($setting);
			}
		}
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return Reflection_Class
	 */
	public function getClass()
	{
		return new Reflection_Class($this->getClassName());
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName()
	{
		// TODO LOWEST remove : this is for unserialize() compatibility with old public $class_name
		if (!isset($this->class_name) && isset(get_object_vars($this)['class_name'])) {
			$this->class_name = Builder::current()->sourceClassName(get_object_vars($this)['class_name']);
		}
		return Builder::className($this->class_name);
	}

	//----------------------------------------------------------------------------- getCustomSettings
	/**
	 * Gets custom settings list
	 *
	 * @param $feature string
	 * @return Selected_Setting[] key is the name of the setting, value is '' or 'selected'
	 */
	public function getCustomSettings($feature = null)
	{
		$list = [];
		$search['code'] = $this->getSourceClassName() . DOT . static::customId($feature) . '.%';
		foreach (Dao::search($search, Setting::class) as $setting) {
			/** @var $setting Setting */
			/** @var $settings Custom_Settings */
			$settings = $setting->value;
			$list[$settings->name] = new Selected_Setting($setting, $settings->name == $this->name);
		}
		ksort($list);
		return $list;
	}

	//---------------------------------------------------------------------------- getSourceClassName
	/**
	 * @return string
	 */
	public function getSourceClassName()
	{
		// TODO LOWEST remove : this is for unserialize() compatibility with old public $class_name
		if (!isset($this->class_name) && isset(get_object_vars($this)['class_name'])) {
			$this->class_name = Builder::current()->sourceClassName(get_object_vars($this)['class_name']);
		}
		return $this->class_name;
	}

	//------------------------------------------------------------------------------------------ load
	/**
	 * Loads a Custom_Settings from the Settings set
	 *
	 * If no Custom_Settings named $name is stored, a new one will be returned
	 *
	 * @param $class_name string
	 * @param $feature    string
	 * @param $name       string
	 * @return static
	 */
	public static function load($class_name, $feature, $name = null)
	{
		/** @var $setting Setting */
		$setting = Search_Object::create(Setting::class);
		$setting->code = $class_name . DOT . static::customId($feature) . ($name ? (DOT . $name) : '');
		$setting = Dao::searchOne($setting);
		$custom_settings = isset($setting)
			? $setting->value
			: Builder::create(get_called_class(), [$class_name]);
		$custom_settings->setting = self::currentUserSetting($class_name, $feature);
		$custom_settings->setting->value = $custom_settings;
		return $custom_settings;
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * If $save_name is set : saves the Custom_Settings object into the Settings set
	 * If $save_name is not set : saves the Custom_Settings object for current user and session
	 *
	 * @param $save_name string
	 */
	public function save($save_name = null)
	{
		if (isset($save_name)) {
			$this->name = $save_name;
			$setting = new Setting(
				$this->getSourceClassName()
				. DOT . static::customId($this->setting->getFeature())
				. ($save_name ? (DOT . $save_name) : '')
			);
			$setting = Dao::searchOne($setting) ?: $setting;
			$setting->value = $this;
			Dao::write($setting);
		}
		elseif ($this) {
			Dao::write($this->setting);
		}
	}

	//-------------------------------------------------------------- selectedSettingsToCustomSettings
	/**
	 * @param $selected_settings Selected_Setting[]
	 * @return Custom_Settings[]
	 */
	public function selectedSettingsToCustomSettings($selected_settings)
	{
		$custom_settings = [];
		foreach ($selected_settings as $selected_setting) {
			$custom_settings[] = $selected_setting->setting->value;
		}
		return $custom_settings;
	}

	//---------------------------------------------------------------------------------- setClassName
	/**
	 * @param $class_name string
	 */
	public function setClassName($class_name)
	{
		$this->class_name = Builder::current()->sourceClassName($class_name);
	}

}
