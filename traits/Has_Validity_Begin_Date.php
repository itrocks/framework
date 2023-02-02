<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Tools\Date_Time;

/**
 * For classes that need a validity begin date
 */
trait Has_Validity_Begin_Date
{

	//-------------------------------------------------------------------------- $validity_begin_date
	public Date_Time|string $validity_begin_date;

}
