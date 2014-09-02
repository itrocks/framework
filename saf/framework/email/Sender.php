<?php
namespace SAF\Framework\Email;

use Mail;
use Mail_smtp;
use PEAR;
use SAF\Framework\Email;
use SAF\Framework\Plugin\Configurable;

if (!@include_once(__DIR__ . '../vendor/pear/Mail.php')) {
	@include_once '/usr/share/php/Mail.php';
}

/**
 * Sends emails
 *
 * This offers a SAF interface to the PHP PEAR Mail package
 * To install it on a Debian Linux server : apt-get install php-mail
 */
class Sender implements Configurable
{

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
				isset($configuration['host']) ?     $configuration['host']     : '',
				isset($configuration['login']) ?    $configuration['login']    : '',
				isset($configuration['password']) ? $configuration['password'] : '',
				isset($configuration['port']) ?     $configuration['port']     : null
			);
			if (isset($configuration['bcc'])) $this->bcc = $configuration['bcc'];
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
		$account = $email->account->smtp_accounts[0];
		$params['host'] = $account->host;
		if ($account->login) {
			$params['auth']     = true;
			$params['username'] = $account->login;
			$params['password'] = $account->password;
		}
		if (isset($this->bcc)) {
			foreach ($this->bcc as $bcc) {
				$recipient = new Recipient();
				$recipient->email = $bcc;
				array_push($email->blind_copy_to, $recipient);
			}
		}
		/** @var $mail Mail_smtp */
		$mail = (new Mail())->factory('smtp', $params);
		$send_result = $mail->send(
			$email->getRecipientsAsStrings(), $email->getHeadersAsStrings(), $email->content
		);
		return !(new PEAR)->isError($send_result);
	}

}
