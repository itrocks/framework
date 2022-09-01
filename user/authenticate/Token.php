<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Traits\Has_Code;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;
use ITRocks\Framework\Traits\Has_Validity_End_Date;
use ITRocks\Framework\User;

/**
 * @override validity_end_date @default defaultValidityEndDate
 * @store_name user_tokens
 */
class Token
{
	use Has_Code;
	use Has_Creation_Date_Time;
	use Has_Validity_End_Date;

	//----------------------------------------------------------------------------------- $single_use
	/**
	 * @var boolean
	 */
	public $single_use = true;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var User
	 */
	public $user;

	//------------------------------------------------------------------------ defaultValidityEndDate
	/**
	 * The default lifetime of a token is 1 minute for single-use tokens, 1 month if multiple-use
	 *
	 * @noinspection PhpUnused @default
	 * @return Date_Time
	 */
	public function defaultValidityEndDate()
	{
		return Date_Time::now()->add(1, $this->single_use ? Date_Time::MINUTE : Date_Time::MONTH);
	}

}
