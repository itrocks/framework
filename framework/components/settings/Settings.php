<?php
namespace SAF\Framework;

/**
 * Set of settings
 */
class Settings extends Set
{

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $elements Setting[]
	 */
	public function __construct($elements = null)
	{
		parent::__construct(Builder::className('SAF\Framework\Setting'), $elements);
	}

	//------------------------------------------------------------------------------------------- add
	/**
	 * Sets a value for a given setting
	 *
	 * @param $code  string|integer|Setting
	 * @param $value string|Setting
	 */
	public function add($code, $value = null)
	{
		if ($value instanceof Setting) {
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
	 * @param string $code
	 * @return Setting
	 */
	public function get($code)
	{
		return parent::get($code);
	}

}
