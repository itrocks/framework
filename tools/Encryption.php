<?php
namespace SAF\Framework\Tools;

/**
 * Encryption class
 */
abstract class Encryption
{

	const BASE64 = 'base64';
	const CRYPT  = 'crypt';
	const MD5    = 'md5';
	const SHA1   = 'sha1';

	//--------------------------------------------------------------------------------------- encrypt
	/**
	 * @param $data      string the data to encrypt
	 * @param $algorithm string an Encryption::XXX constant
	 * @return string the encrypted data
	 */
	public static function encrypt($data, $algorithm)
	{
		switch ($algorithm) {
			case Encryption::BASE64: return base64_encode($data);
			case Encryption::CRYPT:  return crypt($data);
			case Encryption::MD5:    return md5($data);
			case Encryption::SHA1:   return sha1($data);
		}
		return $data;
	}

}
