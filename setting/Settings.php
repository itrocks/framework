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
	public function __construct(array $elements = [])
	{
		$settings = [];
		foreach ($elements as $setting) {
			$settings[$setting->code] = $setting;
		}
		parent::__construct(Builder::className(Setting::class), $settings);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Sets a value for a given setting
	 *
	 * @noinspection PhpDocSignatureInspection $code, $value Setting : specialize
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $code, $value : specialize
	 * @param $code  integer|Setting|string
	 * @param $value Setting|string
	 */
	public function add(int|object|string $code, object|string $value = null) : void
	{
		if ($code instanceof Setting) {
			parent::add($code->code, $code);
		}
		elseif ($value instanceof Setting) {
			parent::add($code, $value);
		}
		else {
			$setting = $this->get($code);
			if (isset($setting)) {
				$setting->value = $value;
			}
			else {
				parent::add($code, new Setting($code, $value));
			}
		}
	}

	//------------------------------------------------------------------------------------------- get
	/**
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection $code : specialize
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
