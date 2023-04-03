<?php
namespace ITRocks\Framework\Traits\Has_Number;

use ITRocks\Framework\Objects\Counter\Use_Counter;
use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Traits\Has_Number;

/**
 * @counter_property number
 * @override number @calculated
 */
#[Override('number', new Mandatory(false), new User(User::READONLY))]
trait Automatic
{
	use Has_Number;
	use Use_Counter;

}
