<?php
namespace ITRocks\Framework\Email;

/**
 * An email net account : host, login and password to access a distant email account
 */
abstract class Net_Account
{

	//----------------------------------------------------------------------------------------- $host
	public string $host = '';

	//---------------------------------------------------------------------------------------- $login
	public string $login = '';

	//------------------------------------------------------------------------------------- $password
	public string $password = '';

	//----------------------------------------------------------------------------------------- $port
	public int $port = 25;

	//----------------------------------------------------------------------------------- __construct
	public function __construct(
		string $host = null, string $login = null, string $password = null, int $port = null
	) {
		if (isset($host))     $this->host     = $host;
		if (isset($login))    $this->login    = $login;
		if (isset($password)) $this->password = $password;
		if (isset($port))     $this->port     = $port;
	}

	//------------------------------------------------------------------------------------ __toString
	public function __toString() : string
	{
		return $this->host . ':' . $this->login;
	}

}
