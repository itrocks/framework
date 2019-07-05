<?php
namespace ITRocks\Framework\Tools;

use InvalidArgumentException;

/**
 * Used for common 'password-like' data encryption
 */
class Password
{

	//------------------------------------------------------- T_* generator characters type constants
	const T_ALL       = 15;
	const T_LOWERCASE = 1;
	const T_NUMERIC   = 4;
	const T_SPECIAL   = 8;
	const T_UPPERCASE = 2;

	//------------------------------------------------------------------------------------- UNCHANGED
	/**
	 * Use this constant when you want to test or set the password as 'unchanged'
	 */
	const UNCHANGED = '~#~*~#~';

	//------------------------------------------------------------------------- $encryption_algorithm
	/**
	 * @var string
	 */
	public $encryption_algorithm = Encryption::SHA1;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	private $password;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $password             string
	 * @param $encryption_algorithm string
	 */
	public function __construct($password = null, $encryption_algorithm = null)
	{
		if (isset($password))             $this->password             = $password;
		if (isset($encryption_algorithm)) $this->encryption_algorithm = $encryption_algorithm;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * The string representation of a Password is the password itself, not encrypted
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->password;
	}

	//------------------------------------------------------------------------------------- encrypted
	/**
	 * Returns the password encrypted using the actual algorithm
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	public function encrypted()
	{
		return Encryption::encrypt($this->password, $this->encryption_algorithm);
	}

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Replaces the password by a randomly generated one
	 *
	 * @param $length          integer wished length for the password
	 * @param $characters_type integer a sum of self::T_* constants to tell which characters are allowed
	 * @param $specials        string special characters that can be used
	 * @return Password
	 */
	public function generate(
		$length = 9, $characters_type = self::T_ALL, $specials = '()[]<>{}_+-*/@$=#!:;,.&'
	) {
		$string = (($characters_type & self::T_LOWERCASE) ? 'abcdefghijklmnopqrstuvwxyz' : '')
			. (($characters_type & self::T_UPPERCASE)       ? 'ABCDEFGHIJKLMNOPQRSTUVWXYZ' : '')
			. (($characters_type & self::T_NUMERIC)         ? '0123456789' : '')
			. (($characters_type & self::T_SPECIAL)         ? $specials : '');
		$maximum_position = strlen($string) - 1;
		$this->password   = '';
		for ($i = 1; $i <= $length; $i++) {
			$position        = mt_rand(0, $maximum_position);
			$this->password .= $string[$position];
		}
		return $this;
	}

}
