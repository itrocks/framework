<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data;

use ITRocks\Framework\Feature\Validate\Property\Max_Length;
use ITRocks\Framework\Reflection\Attribute\Class_\Sort;
use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Default_;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ITRocks\Framework\User;

/** Sensitive data encryption keys */
#[Sort('user'), Store('sensitive_data_keys')]
class Key
{

	//--------------------------------------------------------------------------------------- IV_SIZE
	const IV_SIZE = 16;

	//---------------------------------------------------------------------------------------- METHOD
	const METHOD = 'aes256';

	//----------------------------------------------------------------------------------- $class_name
	#[Mandatory]
	public string $class_name = '';

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * If null, all sensitive data into the class will be accessible or not for one user
	 * If set, each property can be associated to different users
	 */
	public ?string $property_name = null;

	//--------------------------------------------------------------------------------------- $secret
	#[Max_Length(10000)]
	public string $secret = '';

	//----------------------------------------------------------------------------------------- $user
	public User $user;

	//---------------------------------------------------------------------------------- $valid_until
	#[Default_([Date_Time::class, 'max'])]
	public Date_Time|string $valid_until;

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->user->login
			. ($this->class_name ? (':' . $this->class_name . '.' . $this->property_name) : '');
	}

	//------------------------------------------------------------------------------------- getSecret
	public function getSecret() : ?string
	{
		if (!Sensitive_Data::password()) {
			return null;
		}
		$iv = hex2bin(substr($this->secret, 0, static::IV_SIZE * 2));
		$secret = openssl_decrypt(
			substr($this->secret, static::IV_SIZE * 2),
			static::METHOD,
			Sensitive_Data::password(),
			0,
			$iv
		);
		if ($secret === false) {
			$_POST['sensitive_password'] = '';
			Sensitive_Data::password();
		}
		return $secret;
	}

	//------------------------------------------------------------------------------------- setSecret
	public function setSecret(string $secret) : void
	{
		if (!Sensitive_Data::password()) {
			return;
		}
		// TODO must get the previous secret key and update all data that use this key in database
		/** @noinspection PhpUnhandledExceptionInspection valid call */
		$iv           = random_bytes(static::IV_SIZE);
		$this->secret = bin2hex($iv) . openssl_encrypt(
			$secret, static::METHOD, Sensitive_Data::password(), 0, $iv
		);
	}

}
