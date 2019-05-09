<?php
namespace ITRocks\Framework\User\Authenticate;

use ITRocks\Framework\Traits\Has_Code;
use ITRocks\Framework\Traits\Has_Creation_Date_Time;
use ITRocks\Framework\User;

/**
 * @store_name user_tokens
 */
class Token
{
	use Has_Code;
	use Has_Creation_Date_Time;

	//----------------------------------------------------------------------------------------- $user
	/**
	 * @link Object
	 * @var User
	 */
	public $user;

}
