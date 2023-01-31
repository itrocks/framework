<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Objects\Phone\Has_Phone_Number;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Traits\Is_Immutable;

/**
 * Phone : contains a phone number
 */
#[Store('phone_numbers')]
class Phone
{
	use Has_Phone_Number;
	use Is_Immutable;

}
