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
	 * @noinspection PhpUnused @before_write
	 * @return string[]|null
	 */
	public function calculateCreationDateTime() : ?array
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			$this->creation = Date_time::now();
			$only[]         = 'creation';
		}
		return isset($only) ? $only : null;
	}

}
