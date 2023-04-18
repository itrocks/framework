<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Property\User;

/**
 * If you need to have the updated by user information for each of your objects
 *
 * @before_write setUpdatedBy
 */
trait Has_Updated_By
{

	//----------------------------------------------------------------------------------- $updated_by
	#[User(User::INVISIBLE_EDIT, User::INVISIBLE_OUTPUT, User::READONLY)]
	public ?Framework\User $updated_by;

	//---------------------------------------------------------------------------------- setUpdatedBy
	/**
	 * set $last_update_user at beginning of each Dao::write() call
	 *
	 * @noinspection PhpUnused @before_write
	 * @return ?string[] properties added to Only
	 */
	public function setUpdatedBy() : ?array
	{
		if (!Dao::is($this->updated_by, $current_user = Framework\User::current())) {
			$this->updated_by = $current_user;
			return ['updated_by'];
		}
		return null;
	}

}
