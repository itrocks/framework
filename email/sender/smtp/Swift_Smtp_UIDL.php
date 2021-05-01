<?php
namespace ITRocks\Framework\Email\Sender\Smtp;

use Swift_Events_ResponseEvent;
use Swift_Events_ResponseListener;

/**
 * This swift plugin gets the SMTP server UIDL of the sent message (when sent)
 */
class Swift_Smtp_UIDL implements Swift_Events_ResponseListener
{

	//------------------------------------------------------------------------------ responseReceived
	/**
	 * @noinspection PhpParameterNameChangedDuringInheritanceInspection coding standard
	 * @param $event Swift_Events_ResponseEvent
	 */
	public function responseReceived(Swift_Events_ResponseEvent $event)
	{
		if (preg_match('/ queued as (.*)/', $event->getResponse(), $queued)) {
			/** @var $source Swift_Smtp_Transport */
			$source            = $event->getSource();
			$source->last_uidl = trim($queued[1], " \t\n\r\0\x0B<>");
		}
	}

}
