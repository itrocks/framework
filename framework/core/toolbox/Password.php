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
		return $password;
	}

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Generates a random password
	 *
	 * @param $length   integer wished lengthfor the password
	 * @param $specials string special characters that can be used
	 * @return string A randomly generated password
	 */
	public static function generate($length = 9, $specials = "()[]-_+-*/\\")
	{
		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" . $specials;
		$maximum_position = strlen($string) - 1;
		$password = "";
		for ($i = 1; $i <= $length; $i++) {
			$position = mt_rand(0, $maximum_position);
			$password .= $string[$position];
		}
		return $password;
	}

}
