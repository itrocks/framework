<?php
namespace SAF\Framework\Tools;

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

	//------------------------------------------------------------------------------------------ SHA1
	const SHA1 = 'sha1';

	//--------------------------------------------------------------------------------------- encrypt
	/**
	 * @param $data      string the data to encrypt
	 * @param $algorithm string an Encryption::XXX constant
	 * @return string the encrypted data
	 */
	public static function encrypt($data, $algorithm)
	{
		switch ($algorithm) {
			case Encryption::BASE64:         return base64_encode($data);
			case Encryption::CRYPT:          return crypt($data);
			case Encryption::MD5:            return md5($data);
			case Encryption::MYSQL_PASSWORD: return self::mysqlPassword($data);
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
