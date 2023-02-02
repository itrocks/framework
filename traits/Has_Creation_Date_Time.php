<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * If you need to have a creation date-time for each of your objects
 *
 * @before_write calculateCreationDateTime
 */
trait Has_Creation_Date_Time
{

	//------------------------------------------------------------------------------------- $creation
	/**
	 * @default Date_Time::now
	 * @user invisible_edit, invisible_output, readonly
	 */
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
