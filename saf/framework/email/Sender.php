<?php
namespace SAF\Framework\Email;

use Mail;
use Mail_smtp;
use PEAR;
use PEAR_Error;
use SAF\Framework\Builder;
use SAF\Framework\Email;
use SAF\Framework\Plugin\Configurable;

if (!@include_once(__DIR__ . '/../../../vendor/pear/Mail.php')) {
	@include_once '/usr/share/php/Mail.php';
}

/**
 * Sends emails
 *
 * This offers a SAF interface to the PHP PEAR Mail package
 * To install it on a Debian Linux server : apt-get install php-mail
 * AND : apt-get install php-pear & pear install Mail Net_SMTP
 */
class Sender implements Configurable
{

	//----------------------------------------------- Email sender configuration array keys constants
	const BCC      = 'bcc';
	const HOST     = 'host';
	const LOGIN    = 'login';
	const PASSWORD = 'password';
	const PORT     = 'port';

	//------------------------------------------------------------------------------------------ $bcc
	/**
	 * @var string[]
	 */
	public $bcc;

	//------------------------------------------------------------------------- $default_smtp_account
	/**
	 * @var Smtp_Account
	 */
	public $default_smtp_account;

	//------------------------------------------------------------------------------- $last_send_date
	/**
	 * @var string[] ISO datetime, indice is the SMTP account as a string
	 */
	private static $last_error_date = [];

	//------------------------------------------------------------------------------------ $last_host
	/**
	 * @var Smtp_Account[], indice is the Email account as a string
	 */
	private static $last_host = [];

	//------------------------------------------------------------------------------- $last_send_date
	/**
	 * @var string[] ISO datetime, indice is the SMTP account as a string
	 */
	private static $last_send_date = [];

	//----------------------------------------------------------------------------------- __construct
	/**
	 *
	 * @param $configuration string[]|integer[]
	 */
	public function __construct($configuration = [])
	{
		if ($configuration) {
			$this->default_smtp_account = new Smtp_Account(
				isset($configuration[self::HOST]) ?     $configuration[self::HOST]     : '',
				isset($configuration[self::LOGIN]) ?    $configuration[self::LOGIN]    : '',
				isset($configuration[self::PASSWORD]) ? $configuration[self::PASSWORD] : '',
				isset($configuration[self::PORT]) ?     $configuration[self::PORT]     : null
			);
			if (isset($configuration[self::BCC])) $this->bcc = $configuration[self::BCC];
		}
	}

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $email  Email
	 * @param $policy Policy
	 * @return boolean|string true if sent, false if error, 'delayed' if ready for asynchronous send
	 */
	public function send(Email $email, Policy $policy = null)
	{
		if (!isset($policy)) {
			$policy = new Policy();
		}
		$account = ($email->account && $email->account->smtp_accounts)
			? $email->account->smtp_accounts[0]
			: $this->default_smtp_account;
		$params['host'] = $account->host;
		$params['port'] = $account->port;
		if ($account->login) {
			$params['auth']     = true;
			$params['username'] = $account->login;
			$params['password'] = $account->password;
		}
		if (isset($this->bcc)) {
			foreach ($this->bcc as $bcc) {
				array_push($email->blind_copy_to, new Recipient($bcc));
			}
		}

		/** @var $encoder Encoder */
		$encoder = Builder::create(Encoder::class, $email);
		$content = $encoder->encode();

		/** @var $mail Mail_smtp */
		$mail = (new Mail())->factory('smtp', $params);

		$error_reporting = error_reporting();
		error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT);
		$send_result = $mail->send(
			$email->getRecipientsAsStrings(), $email->getHeadersAsStrings(), $content
		);
		error_reporting($error_reporting);

		if ($send_result instanceof PEAR_Error) {
			user_error($send_result->code . ' : ' . $send_result->message, E_USER_ERROR);
			return false;
		}
		return true;
	}

}
