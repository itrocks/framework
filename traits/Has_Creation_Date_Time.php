<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * If you need to have a creation date-time for each of your objects
 *
 * @before_write calculateCreationDateTime
 * @business
 */
trait Has_Creation_Date_Time
{

	//------------------------------------------------------------------------------------- $creation
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @user invisible_edit, invisible_output, readonly
	 * @var Date_Time
	 */
	public $creation;

	//--------------------------------------------------------------------- calculateCreationDateTime
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @return string[]|null
	 */
	public function calculateCreationDateTime()
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			/** @noinspection PhpUnhandledExceptionInspection valid call */
			$this->creation = new Date_Time();
			$only[]         = 'creation';
		}
		return isset($only) ? $only : null;
	}

}
