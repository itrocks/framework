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
	 * @link DateTime
	 * @var Date_Time
	 */
	public $creation;

	//---------------------------------------------------------------------------------- $last_update
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $last_update;

	//------------------------------------------------------------------------- beforeWriteDateLogged
	public function beforeWriteDateLogged()
	{
		if (!isset($this->creation) || $this->creation->isEmpty()) {
			$this->creation = new Date_Time();
		}
		$this->last_update = new Date_Time();
	}

}
