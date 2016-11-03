<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Email;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;

/**
 * Enables archiving of all received and sent emails
 * - when an email is received or sent, save it into current Dao
 *
 * TODO when Received is done, register its archive
 */
class Archive implements Registerable
{

	//------------------------------------------------------------------------------------------ save
	/**
	 * @param $email Email
	 */
	public function save(Email $email)
	{
		Dao::write($email);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$aop = $register->aop;
		$aop->afterMethod([Sender::class, 'send'], [$this, 'save']);
	}

}
