<?php
namespace ITRocks\Framework\Objects\Phone\Tests;

use ITRocks\Framework\Objects\Phone\Has_Phone_Number;

/**
 * Dummy phone
 *
 * @todo rename
 */
class Phone_Dummy
{
	use Has_Phone_Number;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * Phone_Dummy constructor.
	 *
	 * @param $phone_number ?string
	 */
	public function __construct(public ?string $phone_number)
	{}

}
