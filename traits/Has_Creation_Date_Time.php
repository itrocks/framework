<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;

/**
 * If you need to have a creation date-time for each of your objects
 *
 * @before_write calculateCreationDateTime
 */
trait Has_Creation_Date_Time
{

	//------------------------------------------------------------------------------------- $creation
	#[Default_([Date_Time::class, 'now'])]
	#[User(User::INVISIBLE_EDIT, User::INVISIBLE_OUTPUT, User::READONLY)]
	public Date_Time|string $creation;

	//--------------------------------------------------------------------- calculateCreationDateTime
	/**
	 * @noinspection PhpUnused @before_write
	 * @return ?string[]
	 */
	public function calculateCreationDateTime() : ?array
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			$this->creation = Date_time::now();
			return ['creation'];
		}
		return null;
	}

}
