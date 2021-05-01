<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Email;

/**
 * An interface common to all Email senders
 */
interface Sender_Interface
{

	//------------------------------------------------------------------------------------------ send
	/**
	 * Sends an email
	 *
	 * @param $email Email
	 * @return boolean|string true if sent, error message if string, false if not send without error
	 */
	public function send(Email $email) : bool|string;

}
