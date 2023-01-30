<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Attribute\Class_\Store_Name;
use ITRocks\Framework\Setting;

/**
 * User setting
 *
 * @before_write invalidateObjects
 */
#[Store_Name('user_settings')]
class User extends Setting
{
	use Component;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * The saved setting that was loaded by the user, if exists.
	 * If null, then the user setting has been build "from scratch" (default setting).
	 *
	 * @link Object
	 * @var ?Setting
	 */
	public ?Setting $setting;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @composite
	 * @link Object
	 * @mandatory false
	 * @var Framework\User
	 */
	public Framework\User $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code    string|null
	 * @param $value   string|Custom\Set|null
	 * @param $setting Setting|null
	 */
	public function __construct(
		string $code = null, string|Custom\Set $value = null, Setting $setting = null
	) {
		parent::__construct($code, $value);
		if (isset($setting)) {
			$this->setting = $setting;
		}
		if (!isset($this->user)) {
			$this->user = Framework\User::current();
		}
	}

	//----------------------------------------------------------------------------- invalidateObjects
	/**
	 * @noinspection PhpUnused @before_write
	 */
	public function invalidateObjects() : void
	{
		Getter::invalidate($this, 'setting');
		Getter::invalidate($this, 'user');
	}

}
