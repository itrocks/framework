<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework;
use ITRocks\Framework\Reflection\Attribute\Property\User;

/**
 * If you need to have the created by user information for each of your objects
 *
 * @before_write setCreatedBy
 */
trait Has_Created_By
{

	//----------------------------------------------------------------------------------- $created_by
	#[User(User::INVISIBLE_EDIT, User::INVISIBLE_OUTPUT, User::READONLY)]
	public ?Framework\User $created_by;

	//---------------------------------------------------------------------------------- setCreatedBy
	/**
	 * @noinspection PhpUnused @before_write
	 * @return ?string[]
	 */
	public function setCreatedBy() : ?array
	{
		if (!isset($this->created_by)) {
			$this->created_by = Framework\User::current();
			return ['created_by'];
		}
		return null;
	}

}
