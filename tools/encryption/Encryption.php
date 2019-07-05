<?php
namespace ITRocks\Framework\Tools;

use InvalidArgumentException;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\Tools\Encryption\Sensitive_Data;

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

	//-------------------------------------------------------------------------------- SENSITIVE_DATA
	const SENSITIVE_DATA = 'sensitive_data';

	//------------------------------------------------------------------------------------------ SHA1
	const SHA1 = 'sha1';

	//--------------------------------------------------------------------------------------- encrypt
	/**
	 * @param $data      string the data to encrypt
	 * @param $algorithm string an Encryption::XXX constant
	 * @param $property  Reflection_Property
	 * @return string the encrypted data
	 * @throws InvalidArgumentException
	 */
	public static function encrypt($data, $algorithm, Reflection_Property $property = null)
	{
		if (!is_string($data)) {
			throw new InvalidArgumentException('data must be a string string');
		}
		switch ($algorithm) {
			case Encryption::BASE64:         return base64_encode($data);
			case Encryption::CRYPT:          return crypt($data);
			case Encryption::MD5:            return md5($data);
			case Encryption::MYSQL_PASSWORD: return static::mysqlPassword($data);
			case Encryption::SENSITIVE_DATA: return (new Sensitive_Data)->encrypt($data, $property);
			case Encryption::SHA1:           return sha1($data);
		}
		return $data;
	}

	//--------------------------------------------------------------------------------- mysqlPassword
	/**
	 * @param $data string
	 * @return string Mysql PASSWORD hash
	 */
	public static function mysqlPassword($data)
	{
		return '*' . strtoupper(sha1(sha1($data, true)));
	}

}
