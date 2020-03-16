<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Edit;
use ITRocks\Framework\Feature\Lock\Lockable;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Feature\Unlock\Controller;
use ITRocks\Framework\Feature\Unlock\Unlockable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\View;

/**
 * The unlock plugin enables to unlock unlockable objects
 */
class Unlock implements Registerable
{

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * @param $object    Lockable|Unlockable
	 * @param $joinpoint After_Method
	 */
	public function afterOutputControllerGetGeneralButtons($object, After_Method $joinpoint)
	{
		$buttons =& $joinpoint->result;
		if (
			!is_a($object, Unlockable::class)
			|| !$object->locked
			|| isA($joinpoint->pointcut[0], Edit\Controller::class)
			|| isset($buttons[Controller::FEATURE])
		) {
			return;
		}
		$buttons[Controller::FEATURE] = new Button(
			'Unlock',
			View::link($object, Controller::FEATURE),
			Controller::FEATURE,
			Target::RESPONSES
		);
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register)
	{
		$register->aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'],
			[$this, 'afterOutputControllerGetGeneralButtons']
		);
	}

}
