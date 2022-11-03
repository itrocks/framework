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
 * @before_delete unlinkUserSettings
 * @before_write invalidateValueSetting
 * @feature admin
 */
class Setting implements Validate\Except
{

	//----------------------------------------------------------------------------------------- $code
	/**
	 * @var string
	 */
	public string $code;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @getter getValue
	 * @max_length 1000000000
	 * @notice type must be string first, or it will crash !
	 * @var string|Custom\Set|null string if serialized (for storage)
	 * @notice string must come first, because it is stored as this
	 */
	public string|Custom\Set|null $value = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code  string|null
	 * @param $value Custom\Set|string|null
	 */
	public function __construct(string $code = null, Custom\Set|string $value = null)
	{
		if (isset($code))  $this->code = $code;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return (rLastParse($this->code, DOT) ?: $this->code)
			?: Loc::tr(Names::classToDisplay(get_class($this)));
	}

	//-------------------------------------------------------------------------------------- getClass
	/**
	 * @return string
	 */
	public function getClass() : string
	{
		return explode(DOT, $this->code)[0];
	}

	//------------------------------------------------------------------------------------ getFeature
	/**
	 * @return string
	 */
	public function getFeature() : string
	{
		return explode(DOT, $this->code)[1];
	}

	//-------------------------------------------------------------------------------------- getValue
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return object|string
	 */
	protected function getValue() : object|string
	{
		$value = $this->value;
		if (
			isset($value)
			&& is_string($value)
			&& str_starts_with($value, 'O:')
			&& str_ends_with($value, '}')
		) {
			$this->value = unserialize($value);
			// // A patch for retro-compatibility with protected / private $class_name
			if (!$this->value->getClassName()) {
				/** @noinspection PhpUnhandledExceptionInspection constant property from valid object */
				$class_name = new Reflection_Property($this->value, 'class_name');
				$class_name->setValue(
					$this->value,
					Builder::current()->sourceClassName(
						lParse(rParse(rParse($value, '"class_name";s:'), DQ), DQ)
					)
				);
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

	//---------------------------------------------------------------------------- unlinkUserSettings
	/**
	 * @noinspection PhpUnused @before_delete
	 */
	public function unlinkUserSettings()
	{
		foreach (Dao::search(['setting' => $this], Setting\User::class) as $user_setting) {
			$user_setting->setting = null;
			Dao::write($user_setting, Dao::only('setting'));
		}
	}

}
