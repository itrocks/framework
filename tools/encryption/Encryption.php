<?php
namespace ITRocks\Framework\Tools;

use InvalidArgumentException;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;
use ValueError;

/**
 * Encryption class
 */
abstract class Encryption
{

	//---------------------------------------------------------------------------------------- BASE64
	const BASE64 = 'base64';

	//----------------------------------------------------------------------------------------- CRYPT
	const CRYPT = 'crypt';

	//------------------------------------------------------------------------------------------- MD5
	const MD5 = 'md5';

	//-------------------------------------------------------------------------------- MYSQL_PASSWORD
	const MYSQL_PASSWORD = 'mysql_password';

	//------------------------------------------------------------------------------------------ SALT
	const SALT = 'crypt_salt';

	//-------------------------------------------------------------------------------- SENSITIVE_DATA
	const SENSITIVE_DATA = 'sensitive_data';

	//------------------------------------------------------------------------------------------ SHA1
	const SHA1 = 'sha1';

	//---------------------------------------------------------------------------------------- SHA256
	const SHA256 = 'sha256';

	//---------------------------------------------------------------------------------------- SHA512
	const SHA512 = 'sha512';

	//--------------------------------------------------------------------------------------- encrypt
	/**
	 * @param $data      string the data to encrypt
	 * @param $algorithm string an Encryption::XXX constant
	 * @param $property  Reflection_Property|null
	 * @return string the encrypted data
	 * @throws InvalidArgumentException
	 */
	public static function encrypt(
		string $data, string $algorithm, Reflection_Property $property = null
	) : string
	{
		switch ($algorithm) {
			case Encryption::CRYPT:
				include('pwd.php');
				$salt = $pwd[static::class][static::SALT];
				return crypt($data, $salt);
			case Encryption::BASE64:         return base64_encode($data);
			case Encryption::MYSQL_PASSWORD: return static::mysqlPassword($data);
			case Encryption::SENSITIVE_DATA: return (new Sensitive_Data)->encrypt($data, $property);
		}
		try {
			return hash($algorithm, $data);
		}
		catch (ValueError) {
			return $data;
		}
	}

	//--------------------------------------------------------------------------------- mysqlPassword
	/**
	 * @param $data string
	 * @return string Old Mysql native PASSWORD hash
	 */
	public static function mysqlPassword(string $data) : string
	{
		return '*' . strtoupper(sha1(sha1($data, true)));
	}

}
