<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Composite;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Setting;

/**
 * User setting
 *
 * @before_write invalidateObjects
 */
#[Store('user_settings')]
class User extends Setting
{
	use Component;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * The saved setting that was loaded by the user, if exists.
	 * If null, then the user setting has been build "from scratch" (default setting).
	 */
	public ?Setting $setting;

	//----------------------------------------------------------------------------------------- $user
	#[Composite, Mandatory(false)]
	public ?Framework\User $user;

	//----------------------------------------------------------------------------------- __construct
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
	/** @noinspection PhpUnused @before_write */
	public function invalidateObjects() : void
	{
		Getter::invalidate($this, 'setting');
		Getter::invalidate($this, 'user');
	}

}
