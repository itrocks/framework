<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Reflection\Attribute\Class_\Display_Order;
use ITRocks\Framework\Reflection\Attribute\Property\Alias;
use ITRocks\Framework\Reflection\Attribute\Property\User;
use ITRocks\Framework\Reflection\Reflection_Property;

/** @feature add, delete, save */
#[Display_Order('sensitive_password, new_cipher_key, confirm_cipher_key')]
class Cipher_Key
{

	//--------------------------------------------------------------------------- $confirm_cipher_key
	/** @password */
	public $confirm_cipher_key;

	//------------------------------------------------------------------------------- $new_cipher_key
	/** @password */
	public $new_cipher_key;

	//--------------------------------------------------------------------------- $sensitive_password
	/** @password */
	#[Alias('old_cipher_key')]
	public $sensitive_password;

	//----------------------------------------------------------------------------------- __construct
	public function __construct()
	{
		if (Dao::count(Key::class)) {
			return;
		}
		$sensitive_password = new Reflection_Property($this, 'sensitive_password');
		User::of($sensitive_password)->add(User::INVISIBLE);
	}

}
