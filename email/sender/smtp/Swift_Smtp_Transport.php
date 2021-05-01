<?php
namespace ITRocks\Framework\Email\Sender\Smtp;

use Swift_Mime_SimpleMessage;
use Swift_SmtpTransport;

/**
 * Swift SMTP transport, with UIDL storage
 */
class Swift_Smtp_Transport extends Swift_SmtpTransport
{

	//------------------------------------------------------------------------------------ $last_uidl
	/**
	 * @var string
	 */
	public string $last_uidl = '';

	//------------------------------------------------------------------------------------------ send
	/**
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection coding standard
	 * @param $message           Swift_Mime_SimpleMessage
	 * @param $failed_recipients string[]|null An array of failures by-reference
	 * @return integer
	 */
	public function send(Swift_Mime_SimpleMessage $message, &$failed_recipients = null) : int
	{
		$this->last_uidl = '';
		return parent::send($message, $failed_recipients);
	}

}
