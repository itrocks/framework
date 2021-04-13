<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Setting\Custom;
use ITRocks\Framework\Tools\Names;

/**
 * An application setting
 *
 * @before_write invalidateValueSetting
 */
class Setting implements Validate\Except
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

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString()
	{
		return (rLastParse($this->code, DOT) ?: $this->code)
			?: Loc::tr(Names::classToDisplay(get_class($this)));
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
	 * @noinspection PhpDocMissingThrowsInspection
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
			$this->value = unserialize($value);
			// // A patch for retro-compatibility with protected / private $class_name
			if (!$this->value->getClassName()) {
				/** @noinspection PhpUnhandledExceptionInspection constant property from valid object */
				$class_name = new Reflection_Property($this->value, 'class_name');
				$class_name->setAccessible(true);
				$class_name->setValue(
					$this->value,
					Builder::current()->sourceClassName(
						lParse(rParse(rParse($value, '"class_name";s:'), DQ), DQ)
					)
				);
				$class_name->setAccessible(false);
			}
			if (is_object($this->value)) {
				$this->value->setting->code = str_replace(
					'.data_list', '.list', $this->value->setting->code
				);
			}
		}
		if (is_object($this->value) && !isset($this->value->setting)) {
			$this->value->setting = $this;
		}
		return $this->value;
	}

	//------------------------------------------------------------------------ invalidateValueSetting
	/**
	 * @noinspection PhpUnused @before_write
	 */
	public function invalidateValueSetting()
	{
		if (
			is_object($this->value)
			&& ($setting = $this->value->setting)
			&& ($setting instanceof Setting\User)
		) {
			$setting->invalidateObjects();
		}
	}

}
