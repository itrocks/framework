<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\User;

/**
 * If you need to have a creation user for each of your objects
 *
 * @before_write setCreationUser
 * @business
 */
trait Has_Creation_User
{

	//-------------------------------------------------------------------------------- $creation_user
	/**
	 * @link Object
	 * @user invisible_edit, invisible_output, readonly
	 * @var User
	 */
	public $creation_user;

	//------------------------------------------------------------------------------- setCreationUser
	/**
	 * @noinspection PhpUnused @before_write
	 * @return string[]|null
	 */
	public function setCreationUser() : ?array
	{
		if (!isset($this->creation_user)) {
			$this->creation_user = User::current();
			$only[]         = 'creation_user';
		}
		return isset($only) ? $only : null;
	}
}
