<?php
namespace SAF\Framework;
use PEAR, Mail;

if (!@include_once "/usr/share/php/Mail.php") {
	@include_once("framework/vendor/pear/Mail.php");
}

/**
 * Sends emails
 *
 * This offers a SAF interface to the PHP PEAR Mail package
 * To install it on a Debian Linux server : apt-get install php-mail
 */
abstract class Email_Sender
{

	//------------------------------------------------------------------------------- $last_send_date
	/**
	 * @var string[] ISO datetime, indice is the SMTP account as a string
	 */
	private static $last_error_date = array();

	//------------------------------------------------------------------------------------ $last_host
	/**
	 * @var Email_Smtp_Account[], indice is the Email account as a string
	 */
	private static $last_host = array();

	//------------------------------------------------------------------------------- $last_send_date
	/**
	 * @var string[] ISO datetime, indice is the SMTP account as a string
	 */
	private static $last_send_date = array();

	//------------------------------------------------------------------------------------------ send
	/**
	 * @param $email  Email
	 * @param $policy Email_Policy
	 * @return boolean|string true if sent, false if error, "delayed" if ready for asynchronous send
	 */
	public static function send(Email $email, Email_Policy $policy = null)
	{
		if (!isset($policy)) {
			$policy = new Email_Policy();
		}
		$params["host"] = $email->account->smtp_accounts[0]->host;
		$mail = Mail::factory("smtp", $params);
		$send_result = $mail->send($email->getRecipients(), $email->getHeaders(), $email->body);
		return !PEAR::isError($send_result);
	}

}
