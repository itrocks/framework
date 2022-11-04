<?php
namespace ITRocks\Framework\Email;

use ITRocks\Framework\Dao;
use ITRocks\Framework\Email;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;

/**
 * Enables archiving of all received and sent emails
 * - when an email is received or sent, save it into current Dao
 *
 * TODO when Received is done, register its archive
 */
class Archive implements Registerable
{

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register) : void
	{
		$aop = $register->aop;
		$aop->afterMethod([Sender::class, 'send'], [$this, 'save']);
	}

	//------------------------------------------------------------------------------------------ save
	/**
	 * @param $email Email
	 */
	public function save(Email $email)
	{
		if ($email->send_date->isEmpty()) {
			$email->send_date = Date_Time::now();
		}
		Dao::write($email);
	}

}
