<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\User;

/**
 * If you need to have the created by user information for each of your objects
 *
 * @before_write setCreatedBy
 */
trait Has_Created_By
{

	//----------------------------------------------------------------------------------- $created_by
	/**
	 * @user invisible_edit, invisible_output, readonly
	 */
	public ?User $created_by;

	//---------------------------------------------------------------------------------- setCreatedBy
	/**
	 * @noinspection PhpUnused @before_write
	 * @return ?string[]
	 */
	public function setCreatedBy() : ?array
	{
		if (!isset($this->created_by)) {
			$this->created_by = User::current();
			return ['created_by'];
		}
		return null;
	}

}
