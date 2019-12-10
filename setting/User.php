<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Mapper\Getter;
use ITRocks\Framework\Setting;

/**
 * User setting
 *
 * @before_write invalidateObjects
 * @store_name user_settings
 */
class User extends Setting
{
	use Component;

	//-------------------------------------------------------------------------------------- $setting
	/**
	 * The saved setting that was loaded by the user, if exists
	 * If null, then the user setting has been build "from scratch" (default setting)
	 *
	 * @link Object
	 * @var Setting
	 */
	public $setting;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @composite
	 * @link Object
	 * @mandatory false
	 * @var Framework\User
	 */
	public $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code    string
	 * @param $value   string
	 * @param $setting Setting
	 */
	public function __construct($code = null, $value = null, $setting = null)
	{
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
	public function invalidateObjects()
	{
		Getter::invalidate($this, 'setting');
		Getter::invalidate($this, 'user');
	}

}
