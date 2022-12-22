<?php
namespace ITRocks\Framework\Email\Sender;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Email\Smtp_Account;
use ITRocks\Framework\Session;
use ITRocks\Framework\Tools\Date_Time;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\Transport;

/**
 * Email SMTP sender
 */
class Smtp extends Sender
{

	//----------------------------------------------------------------------- Configuration constants
	const ENCRYPTION = 'encryption';
	const HOST       = 'host';
	const LOGIN      = 'login';
	const PASSWORD   = 'password';
	const PORT       = 'port';

	//------------------------------------------------------------------------------------- TRANSPORT
	const TRANSPORT = 'smtp';

	//------------------------------------------------------------------------- $default_smtp_account
	/**
	 * @var Smtp_Account
	 */
	public Smtp_Account $default_smtp_account;

	//---------------------------------------------------------------------------- $working_directory
	/**
	 * @var string
	 */
	public string $working_directory = '';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Smtp plugin creates a smtp transport.
	 *
	 * @param $configuration string[]|integer[]
	 */
	public function __construct(mixed $configuration = [])
	{
		parent::__construct($configuration);
		$this->default_smtp_account = new Smtp_Account(
			$configuration[self::HOST]       ?? '',
			$configuration[self::LOGIN]      ?? '',
			$configuration[self::PASSWORD]   ?? '',
			$configuration[self::PORT]       ?? null,
			$configuration[self::ENCRYPTION] ?? ''
		);
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * Send an email using its account connection information
	 * or the default SMTP account configuration.
	 *
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $email Email
	 * @return boolean|string true if sent, error message if string
	 */
	public function send(Email $email) : bool|string
	{
		$has_list_unsubscribe = false;
		foreach (array_keys($email->headers) as $header_key) {
			if (!strcasecmp($header_key, 'list-unsubscribe')) {
				$has_list_unsubscribe = true;
				break;
			}
		}
		if (!$has_list_unsubscribe) {
			$email->headers['List-Unsubscribe'] = '<mailto: unsubscribe@'
				. Session::current()->domainName() . '?subject=unsubscribe>';
		}
		// email send configuration
		$account = $this->smtpAccount($email);
		$this->sendConfiguration($email);
		$dsn = $account->host;
		if ($account->port) {
			$dsn .= ':' . $account->port;
		}
		if ($account->login) {
			$dsn = 'smtp://' . $account->login . ':' . $account->password . '@' . $dsn;
		}
		if (!$account->encryption) {
			$dsn .= '?verify_peer=0';
		}
		$transport = Transport::fromDsn($dsn);

		/** @noinspection PhpUnhandledExceptionInspection class */
		$encoder = Builder::create(Encoder::class, [$email, $this->working_directory]);
		$message = $encoder->toMessage();

		try {
			$sent = $transport->send($message);
		}
		catch (TransportExceptionInterface $exception) {
			return $email->send_message = 'Send error : ' . $exception->getMessage();
		}

		/** @noinspection PhpUnhandledExceptionInspection valid */
		$email->send_date    = new Date_Time();
		$email->send_message = '';
		if ($uidl = $sent->getMessageId()) {
			$email->uidl = $uidl;
		}

		return true;
	}

	//----------------------------------------------------------------------------------- smtpAccount
	/**
	 * Override default smtp parameters with values in $email, if any
	 *
	 * @param $email Email
	 * @return Smtp_Account
	 */
	protected function smtpAccount(Email $email) : Smtp_Account
	{
		return ($email->account && $email->account->smtp_accounts)
			? reset($email->account->smtp_accounts)
			: $this->default_smtp_account;
	}

}
