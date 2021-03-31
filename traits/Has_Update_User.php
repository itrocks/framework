<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\User;

/**
 * A trait for creation and modification User logged objects
 *
 * @before_write setLastUpdateUser
 * @business
 */
trait Has_Update_User
{

	//----------------------------------------------------------------------------- $last_update_user
	/**
	 * @link Object
	 * @user invisible_edit, invisible_output, readonly
	 * @var User
	 */
	public $last_update_user;

	//----------------------------------------------------------------------------- setLastUpdateUser
	/**
	 * set $last_update_user at beginning of each Dao::write() call
	 *
	 * @noinspection PhpUnused @before_write
	 * @return string[] properties added to Only
	 */
	public function setLastUpdateUser() : array
	{
		if (!isset($this->last_update_user)) {
			$this->last_update_user = User::current();
		}
		return ['last_update_user'];
	}

}
