<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Tools\Date_Time;

/**
 * If you need to have a creation date-time for each of your objects
 *
 * @before_write calculateCreationDateTime
 */
#[Store]
trait Has_Creation_Date_Time
{

	//------------------------------------------------------------------------------------- $creation
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @user invisible_edit, invisible_output, readonly
	 * @var Date_Time|string
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
