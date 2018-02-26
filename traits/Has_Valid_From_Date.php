<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * For classes that have an optional valid-from date as validity date
 *
 * @business
 * @deprecated replace this by Has_Validity_Begin_Date please
 */
trait Has_Valid_From_Date
{

	//------------------------------------------------------------------------------ $valid_from_date
	/**
	 * @var Date_Time
	 */
	public $valid_from_date;

}
