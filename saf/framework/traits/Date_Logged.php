<?php
namespace SAF\Framework\Traits;

use SAF\Framework\Tools\Date_Time;

/**
 * A trait for creation and modification date logged objects
 *
 * @before_write beforeWriteDateLogged
 * @business
 */
trait Date_Logged
{

	//------------------------------------------------------------------------------------- $creation
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @var Date_Time
	 */
	public $creation;

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @var Date_Time
	 */
	public $last_update;

	//------------------------------------------------------------------------- beforeWriteDateLogged
	/**
	 * Calculate $creation and $last_update dates at beginning of each Dao::write() call
	 */
	public function beforeWriteDateLogged()
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			$this->creation = new Date_Time();
		}
		$this->last_update = new Date_Time();
	}

}
