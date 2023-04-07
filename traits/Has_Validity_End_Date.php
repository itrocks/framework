<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Tools\Date_Time;

/**
 * For classes that need a validity end date
 */
trait Has_Validity_End_Date
{

	//---------------------------------------------------------------------------- $validity_end_date
	#[Default_([Date_Time::class, 'max'])]
	public Date_Time|string $validity_end_date;

}
