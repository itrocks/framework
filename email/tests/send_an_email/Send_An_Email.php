<?php
namespace ITRocks\Framework\Email\Tests;

use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Recipient;
use ITRocks\Framework\Email\Sender\Smtp;

/**
 * Functional test of a email sending. This email includes a lot of parts to be tested :
 * - base64 embedded image
 * - file image
 * - web stored image
 * - utf-8 encoded accents
 * - HTML entities like &amp;, &nbsp;, and accents
 * - Subject, sender and recipient have utf-8 accents
 * - Two attachments with accents in their name
 *
 * Everything must be checked
 *
 * @example connect as super-admin, then
 * http://localhost/studio/ITRocks/Framework/Email/Tests/Send_An_Email/send
 */
class Send_An_Email
{

	//------------------------------------------------------------------------------------------ send
	public function send()
	{
		$smtp = Smtp::get(false);
		if (!$smtp) {
			return 'No smtp plugin';
		}
		$email = new Email();
		$email->content = file_get_contents(__DIR__ . '/send_an_email.html');
		$email->from    = new Recipient('sender@itrocks.org', 'Bàptistè Pïllôt');
		$email->subject = 'The mail with quotes and accent : "accentué"';
		$email->to      = [new Recipient('baptiste@pillot.fr', 'Bàptisté Pîllöt')];

		$email->attachments[] = new Email\Attachment(
			'texte attaché.txt', file_get_contents(__DIR__ . '/texte attaché.txt')
		);
		$email->attachments[] = new Email\Attachment(
			'itr attaché.svg', file_get_contents(__DIR__ . '/itr.svg')
		);
		$smtp->working_directory = __DIR__;
		$smtp->send($email);

		return $email->uidl
			? ('Message queued as &lt;' . $email->uidl . '&gt;')
			: ('Not sent : ' . $email->send_message);
	}

}
