<?php
namespace ITRocks\Framework\Email\Sender;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Email\Sender\Smtp\Swift_Smtp_Transport;
use ITRocks\Framework\Email\Sender\Smtp\Swift_Smtp_UIDL;
use ITRocks\Framework\Email\Smtp_Account;
use ITRocks\Framework\Tools\Date_Time;
use Swift_Mailer;

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
		// email send configuration
		$smtp_account = $this->smtpAccount($email);
		$this->sendConfiguration($email);

		$transport = new Swift_Smtp_Transport(
			$smtp_account->host, $smtp_account->port, $smtp_account->encryption
		);
		if ($smtp_account->login) {
			$transport->setUsername($smtp_account->login);
			$transport->setPassword($smtp_account->password);
		}
		$mailer = new Swift_Mailer($transport);
		/** @noinspection PhpUnhandledExceptionInspection class */
		$mailer->registerPlugin(Builder::create(Swift_Smtp_UIDL::class));

		/** @noinspection PhpUnhandledExceptionInspection class */
		$encoder = Builder::create(Encoder::class, [$email, $this->working_directory]);
		$message = $encoder->toSwiftMessage();

		$send_result = $mailer->send($message, $failures);

		if ($send_result === 0) {
			return $email->send_message = 'Send error : ' . join(' ; ', $failures);
		}
		/** @noinspection PhpUnhandledExceptionInspection valid */
		$email->send_date    = new Date_Time($message->getDate()->getTimestamp());
		$email->send_message = '';
		if ($transport->last_uidl) {
			$email->uidl = $transport->last_uidl;
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
