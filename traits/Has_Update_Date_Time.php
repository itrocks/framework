<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * A trait for creation and modification date logged objects
 *
 * @before_write calculateLastUpdateDateTime
 * @business
 */
trait Has_Update_Date_Time
{

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @user invisible_edit, invisible_output, readonly
	 * @var Date_Time
	 */
	public $last_update;

	//------------------------------------------------------------------- calculateLastUpdateDateTime
	/**
	 * Calculate $last_update dates at beginning of each Dao::write() call
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string[] properties added to Only
	 */
	public function calculateLastUpdateDateTime()
	{
		/** @noinspection PhpUnhandledExceptionInspection valid call */
		$this->last_update = new Date_Time();
		return ['last_update'];
	}

}
