<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * For classes that need a validity end date
 */
trait Has_Validity_End_Date
{

	//---------------------------------------------------------------------------- $validity_end_date
	/**
	 * @default Date_Time::max
	 * @link DateTime
	 * @var Date_Time
	 */
	public $validity_end_date;

}
