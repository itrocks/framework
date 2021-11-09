<?php
namespace ITRocks\Framework\Email;

/**
 * An email smtp account
 */
class Smtp_Account extends Net_Account
{

	//------------------------------------------------------------------------------------------- TLS
	const TLS = 'tls';

	//----------------------------------------------------------------------------------- $encryption
	/**
	 * @values static::const
	 * @var string|null
	 */
	public $encryption = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $host       string
	 * @param $login      string
	 * @param $password   string
	 * @param $port       integer
	 * @param $encryption string @values static::const
	 */
	public function __construct(
		$host = null, $login = null, $password = null, $port = null, $encryption = null
	) {
		parent::__construct();
		if (isset($host))       $this->host       = $host;
		if (isset($login))      $this->login      = $login;
		if (isset($password))   $this->password   = $password;
		if (isset($port))       $this->port       = $port;
		if (isset($encryption)) $this->encryption = $encryption;
	}

}
