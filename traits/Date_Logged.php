<?php
namespace ITRocks\Framework\Traits;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * A trait for creation and update date logged objects
 */
#[Store]
trait Date_Logged
{
	use Has_Creation_Date_Time;
	use Has_Update_Date_Time;

}
