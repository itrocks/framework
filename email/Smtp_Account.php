<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Reflection\Attribute\Property\Values;

/**
 * An email smtp account
 */
class Smtp_Account extends Net_Account
{

	//------------------------------------------------------------------------------------------- TLS
	const TLS = 'tls';

	//----------------------------------------------------------------------------------- $encryption
	#[Values(self::class)]
	public ?string $encryption = null;

	//----------------------------------------------------------------------------------- __construct
	/**
	 * @param $host       string|null
	 * @param $login      string|null
	 * @param $password   string|null
	 * @param $port       integer|null
	 * @param $encryption string|null @values static::const
	 */
	public function __construct(
		string $host = null, string $login = null, string $password = null, int $port = null,
		string $encryption = null
	) {
		parent::__construct();
		if (isset($host))       $this->host       = $host;
		if (isset($login))      $this->login      = $login;
		if (isset($password))   $this->password   = $password;
		if (isset($port))       $this->port       = $port;
		if (isset($encryption)) $this->encryption = $encryption;
	}

}
