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

	//---------------------------------------------------------------------------- $creation_datetime
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $creation_datetime;

	//------------------------------------------------------------------------ $modification_datetime
	/**
	 * @link DateTime
	 * @var Date_Time
	 */
	public $modification_datetime;

	//------------------------------------------------------------------------- beforeWriteDateLogged
	public function beforeWriteDateLogged()
	{
		if (!isset($this->creation_datetime) || $this->creation_datetime->isEmpty()) {
			$this->creation_datetime = new Date_Time();
		}
		else {
			$this->modification_datetime = new Date_Time();
		}
	}

}
