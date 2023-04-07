<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Reflection\Attribute\Class_\Override;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Has_Code;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;
use ITRocks\Framework\Traits\Has_Validity_End_Date;
use ITRocks\Framework\User;

#[Override('validity_end_date', new Default_('defaultValidityEndDate')), Store('user_tokens')]
class Token
{
	use Has_Code;
	use Has_Creation_Date_Time;
	use Has_Validity_End_Date;

	//----------------------------------------------------------------------------------- $single_use
	public bool $single_use = true;

	//----------------------------------------------------------------------------------------- $user
	public User $user;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->code;
	}
	
	//------------------------------------------------------------------------ defaultValidityEndDate
	/**
	 * The default lifetime of a token is 1 minute for single-use tokens, 1 month if multiple-use
	 *
	 * @noinspection PhpUnused #Default
	 */
	public function defaultValidityEndDate() : Date_Time
	{
		return Date_Time::now()->add(1, $this->single_use ? Date_Time::MINUTE : Date_Time::MONTH);
	}

}
