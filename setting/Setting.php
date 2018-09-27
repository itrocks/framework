<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting\Custom;

/**
 * An application setting
 */
class Setting
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public $code;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @getter getValue
	 * @max_length 1000000000
	 * @var string|Custom\Set string if serialized (for storage)
	 */
	public $value;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code  string
	 * @param $value string|object
	 */
	public function __construct($code = null, $value = null)
	{
		if (isset($code))  $this->code = $code;
		if (isset($value)) $this->value = $value;
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return string
	 */
	public function getClass()
	{
		return explode(DOT, $this->code)[0];
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * @return string
	 */
	public function getFeature()
	{
		return explode(DOT, $this->code)[1];
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @return string|object
	 */
	protected function getValue()
	{
		$value = $this->value;
		if (
			isset($value)
			&& is_string($value)
			&& (substr($value, 0, 2) == 'O:')
			&& (substr($value, -1) === '}')
		) {
			$value = strReplace([
				'O:61:"ITRocks\Framework\Widget\Data_List_Setting\Data_List_Settings"'
					=> 'O:41:"ITRocks\Framework\Widget\List_Setting\Set"',
				'O:51:"ITRocks\Framework\Widget\Data_List_Setting\Property"'
					=> 'O:46:"ITRocks\Framework\Widget\List_Setting\Property"',
				'O:55:"ITRocks\Framework\Widget\Output_Setting\Output_Settings"'
					=> 'O:43:"ITRocks\Framework\Widget\Output_Setting\Set"',
				'O:38:"ITRocks\Framework\Setting\User_Setting"'
					=> 'O:30:"ITRocks\Framework\Setting\User"'
			], $value);
			$this->value = unserialize($value);
			// // A patch for retro-compatibility with protected / private $class_name
			if (!$this->value->getClassName()) {
				$class_name = new Reflection_Property(get_class($this->value), 'class_name');
				$class_name->setAccessible(true);
				$class_name->setValue(
					$this->value,
					Builder::current()->sourceClassName(
						lParse(rParse(rParse($value, '"class_name";s:'), DQ), DQ)
					)
				);
				$class_name->setAccessible(false);
			}
			$this->value->setting->code = str_replace('.data_list', '.list', $this->value->setting->code);
		}
		if (!isset($this->value->setting)) {
			$this->value->setting = $this;
		}
		return $this->value;
	}

}
