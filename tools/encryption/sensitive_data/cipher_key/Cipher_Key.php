<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data;

use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\User;

/** @feature add, delete, save */
#[Display_Order('sensitive_password, new_cipher_key, confirm_cipher_key')]
class Cipher_Key
{

	//--------------------------------------------------------------------------- $confirm_cipher_key
	/** @password */
	public string $confirm_cipher_key = '';

	//------------------------------------------------------------------------------- $new_cipher_key
	/** @password */
	public string $new_cipher_key = '';

	//--------------------------------------------------------------------------- $sensitive_password
	/** @password */
	#[Alias('old_cipher_key')]
	public string $sensitive_password = '';

	//---------------------------------------------------------------------------------------- $token
	#[User(User::HIDDEN)]
	public string $token = '';

}
