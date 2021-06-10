<?php
namespace ITRocks\Framework\Traits\Has_Number;

use ITRocks\Framework\Objects\Counter\Use_Counter;
use ITRocks\Framework\Traits\Has_Number;

/**
 * @counter_property number
 * @override number @calculated @mandatory false @user readonly
 */
trait Automatic
{
	use Has_Number;
	use Use_Counter;

}
