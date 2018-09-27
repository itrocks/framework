<?php
namespace ITRocks\Framework\Setting;

use ITRocks\Framework;
use ITRocks\Framework\Mapper\Component;
use ITRocks\Framework\Setting;

/**
 * User setting
 *
 * @store_name user_settings
 */
class User extends Setting
{
	use Component;

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
	 * @param $code  string
	 * @param $value string
	 */
	public function __construct($code = null, $value = null)
	{
		parent::__construct($code, $value);
		if (!isset($this->user)) {
			$this->user = Framework\User::current();
		}
	}

}
