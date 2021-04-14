<?php
namespace ITRocks\Framework\Email\Send;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Email;
use ITRocks\Framework\Email\Sender;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Date_Time;
use ITRocks\Framework\View;

/**
 * @feature Resend emails not sent because of a SMTP provider error
 */
class Resend_Controller extends Controller implements Registerable
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'resend';

	//------------------------------------------------------------------------------------- addButton
	/**
	 * @param $joinpoint After_Method
	 */
	public function addButton(After_Method $joinpoint)
	{
		if (!($joinpoint->parameters['object'] instanceof Email)) {
			return;
		}
		if (!(Sender\Smtp::get() ?: Sender\File::get())) {
			return;
		}
		/** @var $email Email */
		$email = $joinpoint->parameters['object'];
		if ($email->uidl && !$email->send_message) {
			return;
		}
		$email->date = Date_Time::now();
		$joinpoint->result[] = new Button(
			'Resend', View::link($email, Controller::FEATURE), Controller::FEATURE, Target::RESPONSES
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'],
			[$this, 'addButton']
		);
	}

}
