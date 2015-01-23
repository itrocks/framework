<?php
namespace SAF\Framework\Traits;

use SAF\Framework\Tools\Date_Time;

/**
 * For classes that have an optional valid-from date as validity date
 *
 * @business
 */
trait Has_Valid_From_Date
{

	//------------------------------------------------------------------------------ $valid_from_date
	/**
	 * @var Date_Time
	 */
	public $valid_from_date;

}
