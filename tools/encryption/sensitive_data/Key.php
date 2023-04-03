<?php
namespace ITRocks\Framework\Tools\Encryption\Sensitive_Data;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;
use ITRocks\Framework\Reflection\Attribute\Property\Mandatory;
use ITRocks\Framework\User;

/**
 * Sensitive data encryption keys
 */
#[Store('sensitive_data_keys')]
class Key
{

	//--------------------------------------------------------------------------------------- IV_SIZE
	const IV_SIZE = 16;

	//---------------------------------------------------------------------------------------- METHOD
	const METHOD = 'AES256';

	//----------------------------------------------------------------------------------- $class_name
	#[Mandatory]
	public string $class_name;

	//-------------------------------------------------------------------------------- $property_name
	/**
	 * If null, all sensitive data into the class will be accessible or not for one user
	 * If set, each property can be associated to different users
	 */
	public ?string $property_name;

	//--------------------------------------------------------------------------------------- $secret
	/** @max_length 10000 */
	public string $secret;

	//----------------------------------------------------------------------------------------- $user
	public User $user;

	//------------------------------------------------------------------------------------- getSecret
	public function getSecret() : ?string
	{
		if (!isset($_POST['password'])) {
			return null;
		}
		$iv = hex2bin(substr($this->secret, static::IV_SIZE * 2));
		return openssl_decrypt(
			substr($this->secret, static::IV_SIZE * 2),
			static::METHOD,
			$_POST['password'],
			0,
			$iv
		);
	}

	//------------------------------------------------------------------------------------- setSecret
	public function setSecret(string $secret) : void
	{
		if (!isset($_POST['password'])) {
			return;
		}
		// TODO must get the previous secret key and update all data that use this key in database
		/** @noinspection PhpUnhandledExceptionInspection valid call */
		$iv           = random_bytes(static::IV_SIZE);
		$this->secret = bin2hex($iv) . openssl_encrypt(
			$secret,
			static::METHOD,
			$_POST['password'],
			0,
			$iv
		);
	}

}
