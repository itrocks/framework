<?php
namespace SAF\Framework;

/**
 * Used for common "password-like" data encryption
 */
class Password
{

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

	//------------------------------------------------------------------------------------- encrypted
	/**
	 * Returns the password encrypted using the actual algorithm
	 *
	 * @return string
	 */
	public function encrypted()
	{
		return Encryption::encrypt($this->password, $this->encryption_algorithm);
	}

	//-------------------------------------------------------------------------------------- generate
	/**
	 * Replaces the password by a randomly generated one
	 *
	 * @param $length   integer wished length for the password
	 * @param $specials string special characters that can be used
	 * @return Password
	 */
	public function generate($length = 9, $specials = "()[]-_+-*/\\")
	{
		$string = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789" . $specials;
		$maximum_position = strlen($string) - 1;
		$this->password = "";
		for ($i = 1; $i <= $length; $i++) {
			$position = mt_rand(0, $maximum_position);
			$this->password .= $string[$position];
		}
		return $this;
	}

}
