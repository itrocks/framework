<?php
namespace ITRocks\Framework\Objects;

use ITRocks\Framework\Objects\Phone\Has_Phone_Number;
use ITRocks\Framework\Traits\Has_Number;
use ITRocks\Framework\Traits\Is_Immutable;

/**
 * Phone : contains a phone number
 *
 * @business
 * @store_name phone_numbers
 */
class Phone
{
	use Has_Phone_Number;
	use Is_Immutable;

}
