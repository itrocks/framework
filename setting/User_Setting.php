<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Setting;
use ITRocks\Framework\User;

/**
 * User setting
 *
 * @store_name users_settings
 * @todo store_name user_settings (default)
 */
class User_Setting extends Setting
{
	use Component;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @composite
	 * @link Object
	 * @mandatory false
	 * @var User
	 */
	public $user;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $code  string
	 * @param $value string
	 */
	public function __construct($code = null, $value = null)
	{
		parent::__construct($code, $value);
		if (!isset($this->user)) {
			$this->user = User::current();
		}
	}

}
