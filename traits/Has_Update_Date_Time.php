<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A trait for creation and modification date logged objects
 *
 * @before_write calculateLastUpdateDateTime
 */
trait Has_Update_Date_Time
{

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @default Date_Time::now
	 * @user invisible_edit, invisible_output, readonly
	 */
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
