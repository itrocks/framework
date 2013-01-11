<?php
namespace SAF\Framework;

class Password
{

	//----------------------------------------------------------------------------------------- crypt
	/**
	 * Crypt a password using crypt algorithm
	 *
	 * @param unknown_type $password
	 * @param unknown_type $mode
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

}
