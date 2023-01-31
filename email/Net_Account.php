<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Reflection\Attribute\Class_\Store;

/**
 * An email net account : host, login and password to access a distant email account
 */
#[Store]
abstract class Net_Account
{

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public string $host = '';

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public string $login = '';

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public string $password = '';

	//----------------------------------------------------------------------------------------- $port
	/**
	 * @var integer
	 */
	public int $port = 25;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $host     string|null
	 * @param $login    string|null
	 * @param $password string|null
	 * @param $port     integer|null
	 */
	public function __construct(
		string $host = null, string $login = null, string $password = null, int $port = null
	) {
		if (isset($host))     $this->host     = $host;
		if (isset($login))    $this->login    = $login;
		if (isset($password)) $this->password = $password;
		if (isset($port))     $this->port     = $port;
	}

	//------------------------------------------------------------------------------------ __toString
	/**
	 * @return string
	 */
	public function __toString() : string
	{
		return $this->host . ':' . $this->login;
	}

}
