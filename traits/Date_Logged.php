<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

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
	 * @user readonly
	 * @var Date_Time
	 */
	public $creation;

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @default Date_Time::now
	 * @link DateTime
	 * @user readonly
	 * @var Date_Time
	 */
	public $last_update;

	//------------------------------------------------------------------------- beforeWriteDateLogged
	/**
	 * Calculate $creation and $last_update dates at beginning of each Dao::write() call
	 *
	 * @return string[] properties added to Only
	 */
	public function beforeWriteDateLogged()
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			$this->creation = new Date_Time();
			$only[] = 'creation';
		}
		$this->last_update = new Date_Time();
		$only[] = 'last_update';
		return $only;
	}

}
