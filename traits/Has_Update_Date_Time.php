<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Tools\Date_Time;

/**
 * A trait for creation and modification date logged objects
 *
 * @before_write calculateLastUpdateDateTime
 */
trait Has_Update_Date_Time
{

	//---------------------------------------------------------------------------------- $last_update
	#[Default_([Date_Time::class, 'now'])]
	#[User(User::INVISIBLE_EDIT, User::INVISIBLE_OUTPUT, User::READONLY)]
	public Date_Time|string $last_update;

	//------------------------------------------------------------------- calculateLastUpdateDateTime
	/**
	 * Calculate $last_update dates at beginning of each Dao::write() call
	 *
	 * @noinspection PhpUnused @before_write
	 * @return string[] properties added to Only
	 */
	public function calculateLastUpdateDateTime() : array
	{
		$this->last_update = Date_Time::now();
		return ['last_update'];
	}

}
