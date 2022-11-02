<?php
namespace ITRocks\Framework\Setting\Custom;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Func;
use ITRocks\Framework\Mapper\Search_Object;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Setting\Selected;
use ITRocks\Framework\Traits\Has_Name;
use ITRocks\Framework\User;

/**
 * Custom settings objects can be loaded and saved from user configuration
 */
abstract class Set
{
	use Has_Name;

	//----------------------------------------------------------------------------------- $class_name
	/**
	 * The name of the class which list settings apply
	 *
	 * @var string
	 */
	public $class_name;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * @store false Setting\Custom\Set is always saved into a Setting, we must not save it again
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

	//--------------------------------------------------------------------------------------- current
	/**
	 * Get current session / user custom settings object
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string class name that identifies setting
	 * @param $feature    string
	 * @return static
	 */
	public static function current(string $class_name, string $feature = '') : static
	{
		$class_name = Builder::current()->sourceClassName($class_name);
		$setting    = self::currentUserSetting($class_name, $feature);
		if (isset($setting->value)) {
			$custom_settings = $setting->value;
		}
		else {
			/** @noinspection PhpUnhandledExceptionInspection static */
			$custom_settings = Builder::create(static::class, [$class_name]);
			$setting->value  = $custom_settings;
		}
		$custom_settings->setting = $setting;
		// A patch for retro-compatibility with protected / private $class_name
		if (!$setting->value->class_name) {
			$setting->value->class_name = $class_name;
		}
		return $custom_settings;
	}

	//---------------------------------------------------------------------------- currentUserSetting
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $feature    string
	 * @return Setting\User
	 */
	public static function currentUserSetting(string $class_name, string $feature = '') : Setting\User
	{
		$class_name = Builder::current()->sourceClassName($class_name);
		// use a search array with Func::equal() for SQL optimization
		$code   = $class_name . DOT . static::customId($feature);
		$search = ['code' => Func::equal($code), 'user' => User::current()];
		/** @noinspection PhpUnhandledExceptionInspection */
		/** @var $setting Setting\User */
		$setting = Dao::searchOne($search, Setting\User::class)
			?: Builder::create(Setting\User::class, [$code]);
		return $setting;
	}

	//-------------------------------------------------------------------------------------- customId
	/**
	 * @param $feature string
	 * @return string
	 */
	protected static function customId(string $feature = '') : string
	{
		if (!$feature) {
			/** @example 'Output_Setting' */
			$set_name = rLastParse(lParse(static::class, BS . 'Set'), BS);
			/** @example 'output' */
			$feature = lParse(strtolower($set_name), '_setting');
		}
		return $feature;
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * Delete the Setting\Custom\Set object from the Settings set
	 *
	 * @param $feature string
	 */
	public function delete(string $feature = '')
	{
		if (!$this->name) {
			return;
		}
		$code    = $this->getSourceClassName() . DOT . static::customId($feature);
		$setting = new Setting($code . DOT . $this->name);
		$setting = Dao::searchOne($setting);
		if (isset($setting)) {
			Dao::delete($setting);
		}
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return Reflection_Class
	 */
	public function getClass()
	{
		/** @noinspection PhpUnhandledExceptionInspection */
		return new Reflection_Class($this->getClassName());
	}

	//---------------------------------------------------------------------------------- getClassName
	/**
	 * @return string
	 */
	public function getClassName()
	{
		return Builder::className($this->class_name);
	}

	//----------------------------------------------------------------------------- getCustomSettings
	/**
	 * Gets custom settings list
	 *
	 * @param $feature string
	 * @return Selected[] key is the name of the setting, value is '' or 'selected'
	 */
	public function getCustomSettings(string $feature = '') : array
	{
		$list           = [];
		$search['code'] = $this->getSourceClassName() . DOT . static::customId($feature) . '.%';
		foreach (Dao::search($search, Setting::class) as $setting) {
			/** @var $setting Setting */
			/** @var $settings Setting\Custom\Set */
			$settings              = $setting->value;
			$list[$settings->name] = new Selected($setting, $settings->name === $this->name);
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
	 * Loads a Setting\Custom\Set from the Settings set
	 *
	 * If no Setting\Custom\Set named $name is stored, a new one will be returned
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $class_name string
	 * @param $feature    string
	 * @param $name       string
	 * @return static
	 */
	public static function load(string $class_name, string $feature, string $name = '') : static
	{
		$setting       = Search_Object::create(Setting::class);
		$setting->code = $class_name . DOT . static::customId($feature)
			. ($name ? (DOT . $name) : '');
		$setting = Dao::searchOne($setting);
		/** @noinspection PhpUnhandledExceptionInspection static */
		$custom_settings = $setting->value ?? Builder::create(static::class, [$class_name]);
		$custom_settings->setting          = self::currentUserSetting($class_name, $feature);
		$custom_settings->setting->setting = $setting;
		$custom_settings->setting->value   = $custom_settings;
		return $custom_settings;
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * In all cases : saves the Setting\Custom\Set object for current user and session
	 * If $save_name is set : saves the Setting\Custom\Set object into the Settings set
	 *
	 * @param $save_name string
	 */
	public function save(string $save_name = '')
	{
		if (($save_name !== '') && ($this->setting->value instanceof Set)) {
			$this->setting->value->name = $save_name;
		}
		Dao::write($this->setting);
		if ($save_name === '') {
			return;
		}
		$this->name = $save_name;
		$setting    = new Setting(
			$this->getSourceClassName()
				. DOT . static::customId($this->setting->getFeature())
				. ($save_name ? (DOT . $save_name) : '')
		);
		$setting        = Dao::searchOne($setting) ?: $setting;
		$setting->value = $this;
		Dao::write($setting);
	}

	//-------------------------------------------------------------- selectedSettingsToCustomSettings
	/**
	 * @param $selected_settings Selected[]
	 * @return static[]
	 */
	public function selectedSettingsToCustomSettings(array $selected_settings)
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
