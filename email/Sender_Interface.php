<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Plugin\Configurable;

/**
 * Interface Sender_Interface
 *
 * Sends emails
 *
 */
interface Sender_Interface extends Configurable
{

	/**
	 * @param Email_Interface $email
	 * @return mixed
	 */
	public function send(Email_Interface $email);

}
