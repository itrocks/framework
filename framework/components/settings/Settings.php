<?php
namespace SAF\Framework;

/**
 * Set of settings
 *
 * @override elements @link Collection @type Setting[]
 */
class Settings extends Set
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $elements Setting[]
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
		return parent::get($code);
	}

}
