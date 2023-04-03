<?php
namespace ITRocks\Framework;

use ITRocks\Framework\Feature\Validate;
use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Getter;
use ITRocks\Framework\Setting\Custom;
use ITRocks\Framework\Tools\Names;

/**
 * An application setting
 *
 * @before_delete unlinkUserSettings
 * @before_write invalidateValueSetting
 * @feature admin
 */
#[Store]
class Setting implements Validate\Except
{

	//----------------------------------------------------------------------------------------- $code
	public string $code;

	//---------------------------------------------------------------------------------------- $value
	/**
	 * @notice string must come first, because it is stored as this
	 * @var string|Custom\Set|null Must force type here : php real type does not take care of ordering
	 */
	#[Getter('getValue'), Max_Length(1000000000)]
	public string|Custom\Set|null $value = null;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(string $code = null, Custom\Set|string $value = null)
	{
		if (isset($code))  $this->code = $code;
		if (isset($value)) $this->value = $value;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return (rLastParse($this->code, DOT) ?: $this->code)
			?: Loc::tr(Names::classToDisplay(get_class($this)));
	}

	//-------------------------------------------------------------------------------------- getClass
	public function getClass() : string
	{
		return explode(DOT, $this->code)[0];
	}

	//------------------------------------------------------------------------------------ getFeature
	public function getFeature() : string
	{
		return explode(DOT, $this->code)[1];
	}

	//-------------------------------------------------------------------------------------- getValue
	protected function getValue() : object|string|null
	{
		$value = $this->value;
		if (
			isset($value)
			&& is_string($value)
			&& str_starts_with($value, 'O:')
			&& str_ends_with($value, '}')
		) {
			$this->value = unserialize($value);
		}
		if (is_object($this->value) && !isset($this->value->setting)) {
			$this->value->setting = $this;
		}
		return $this->value;
	}

	//------------------------------------------------------------------------ invalidateValueSetting
	/** @noinspection PhpUnused @before_write */
	public function invalidateValueSetting() : void
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
	/** @noinspection PhpUnused @before_delete */
	public function unlinkUserSettings() : void
	{
		foreach (Dao::search(['setting' => $this], Setting\User::class) as $user_setting) {
			$user_setting->setting = null;
			Dao::write($user_setting, Dao::only('setting'));
		}
	}

}
