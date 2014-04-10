<?php
namespace SAF\Framework\Setting\Module;

use SAF\Framework\Mapper\Component;
use SAF\Framework\Setting\Setting;
use SAF\Framework\User;

/**
 * User setting
 *
 * @set Users_Settings
 */
class User_Setting extends Setting
{
	use Component;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @composite
	 * @link Object
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
