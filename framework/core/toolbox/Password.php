<?php
namespace SAF\Framework;

class Password
{

	//----------------------------------------------------------------------------------------- crypt
	/**
	 * Crypt a password using crypt algorithm
	 *
	 * @param $password  string
	 * @param $algorithm string
	 * @return string
	 */
	public static function crypt($password, $algorithm)
	{
		switch ($algorithm) {
			case "crypt": return crypt($password);
			case "md5":   return md5($password);
			case "sha1":  return sha1($password);
		}
		return self::mysqlPassword($password);
	}

	//--------------------------------------------------------------------------------- mysqlPassword
	private static function mysqlPassword($password)
	{
		return $password;
	}

	//---------------------------------------------------------------------------------------- random
	/**
	 * Generate a random password.
	 * @param int $size the size of returned password
	 * @return string A random password with size equals to size parameter
	 */
	public static function random($size = 9)
	{
		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$size_string = strlen($string);
		$password = "";
		for ($i = 1; $i <= $size; $i++) {
			$random = mt_rand(0, ($size_string - 1));
			$password .= $string[$random];
		}
		return $password;
	}
}
