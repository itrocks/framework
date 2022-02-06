<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Setting;
use ITRocks\Framework\Tools\Set;

/**
 * Set of settings
 *
 * @override elements @link Collection @var Setting[]
 */
class Settings extends Set
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $elements Setting[]
	 */
	public function __construct(array $elements = null)
	{
		$settings = [];
		if (isset($elements)) {
			foreach ($elements as $setting) {
				$settings[$setting->code] = $setting;
			}
		}
		parent::__construct(Builder::className('ITRocks\Framework\Setting'), $settings);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Sets a value for a given setting
	 *
	 * @param $code  integer|Setting|string
	 * @param $value Setting|string
	 * @return Setting
	 */
	public function add(int|object|string $code, object|string $value = null) : Setting
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
	 * @param $code int|string
	 * @return ?Setting
	 */
	public function get(int|string $code) : ?Setting
	{
		/** @var $setting Setting */
		$setting = parent::get($code);
		return $setting;
	}

}
