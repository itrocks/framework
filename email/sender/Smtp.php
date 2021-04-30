<?php
namespace ITRocks\Framework\Email\Sender;

use Exception;
use ITRocks\Framework\Builder;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Encoder;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Tools\Date_Time;
use Swift_Mailer;
use Swift_SmtpTransport;

/**
 * Email SMTP sender
 */
class Smtp extends Sender
{
	//----------------------------------------------------------------------- Configuration constants
	const HOST     = 'host';
	const LOGIN    = 'login';
	const PASSWORD = 'password';
	const PORT     = 'port';

	//----------------------------------------------------------------------------------- __construct
	/**
	 * The constructor of the Smtp plugin creates a smtp transport.
	 *
	 * @param $configuration string[]|integer[]
	 */
	public function __construct($configuration = [])
	{
		parent::__construct($configuration);
		$this->transport = new Swift_SmtpTransport();
		if ($configuration) {
			if ($configuration[self::HOST]) {
				$this->transport->setHost($configuration[self::HOST]);
			}
			if ($configuration[self::LOGIN]) {
				$this->transport->setUsername($configuration[self::LOGIN]);
			}
			if ($configuration[self::PASSWORD]) {
				$this->transport->setPassword($configuration[self::PASSWORD]);
			}
			if ($configuration[self::PORT]) {
				$this->transport->setPort($configuration[self::PORT]);
			}
		}
	}

	//------------------------------------------------------------------------ overrideSmtpParameters
	/**
	 * Override default smtp parameters with values in $email, if any
	 *
	 * @param $email Email
	 */
	private function overrideSmtpParameters (Email $email): void
	{
		if ($email->account && $email->account->smtp_accounts) {
			$account = $email->account->smtp_accounts[0];
			$this->transport->setHost($account->host);
			$this->transport->setPort($account->port);
			if ($account->login) {
				$this->transport->setUsername($account->login);
				$this->transport->setPassword($account->password);
			}
		}
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * Send an email using its account connection information
	 * or the default SMTP account configuration.
	 *
	 * @param $email Email
	 * @return boolean|string true if sent, error message if string
	 * @throws Exception
	 */
	public function send(Email $email): bool|string
	{
		// email send configuration
		$this->overrideSmtpParameters($email);
		$this->sendConfiguration($email);

		$mailer = new Swift_Mailer($this->transport);

		$encoder = Builder::create(Encoder::class, [$email]);
		$message = $encoder->createSwiftMessage();

		$send_result = $mailer->send($message, $failures);

		// user error when errors
		$email->send_message = '';
		if ($send_result === 0) {
			$send_error_msg = 'Send error: ' . print_r($failures);
			return $email->send_message = $send_error_msg;
		}
		$email->send_date = new Date_Time($message->getDate()->getTimestamp());
		if (isset($mail->queued_as)) {
			$email->uidl = $mail->queued_as;
		}
		return true;
	}

}
