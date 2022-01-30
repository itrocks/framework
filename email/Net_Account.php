<?php
namespace ITRocks\Framework\Email;

/**
 * An email net account : host, login and password to access a distant email account
 *
 * @business
 */
abstract class Net_Account
{

	//----------------------------------------------------------------------------------------- $host
	/**
	 * @var string
	 */
	public $host;

	//---------------------------------------------------------------------------------------- $login
	/**
	 * @var string
	 */
	public $login;

	//------------------------------------------------------------------------------------- $password
	/**
	 * @var string
	 */
	public $password;

	//----------------------------------------------------------------------------------------- $port
	/**
	 * @var integer
	 */
	public $port = 25;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $host     string
	 * @param $login    string
	 * @param $password string
	 * @param $port     integer
	 */
	public function __construct($host = null, $login = null, $password = null, $port = null)
	{
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
