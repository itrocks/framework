<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\User;

/**
 * If you need to have the updated by user information for each of your objects
 *
 * @before_write setUpdatedBy
 */
#[Store]
trait Has_Updated_By
{

	//----------------------------------------------------------------------------------- $updated_by
	/**
	 * @link Object
	 * @user invisible_edit, invisible_output, readonly
	 * @var ?User
	 */
	public ?User $updated_by;

	//---------------------------------------------------------------------------------- setUpdatedBy
	/**
	 * set $last_update_user at beginning of each Dao::write() call
	 *
	 * @noinspection PhpUnused @before_write
	 * @return ?string[] properties added to Only
	 */
	public function setUpdatedBy() : ?array
	{
		if (!Dao::is($this->updated_by, $current_user = User::current())) {
			$this->updated_by = $current_user;
			return ['updated_by'];
		}
		return null;
	}

}
