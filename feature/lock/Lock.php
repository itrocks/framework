<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Edit;
use ITRocks\Framework\Feature\Lock\Controller;
use ITRocks\Framework\Feature\Lock\Lockable;
use ITRocks\Framework\Feature\Output;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Tools\Color;
use ITRocks\Framework\View;

/**
 * The lock plugin enables to lock any object
 */
class Lock implements Registerable
{

	//---------------------------------------------------------- afterEditControllerGetGeneralButtons
	/**
	 * @param $object Lockable
	 * @param $result Button[]
	 */
	public function afterEditControllerGetGeneralButtons($object, array &$result)
	{
		if (isA($object, Lockable::class)) {
			/** @var $object Lockable */
			// if the object is locked : remove write and delete buttons
			if ($object->locked) {
				if (isset($result[Feature::F_WRITE])) {
					unset($result[Feature::F_WRITE]);
				}
				if (isset($result[Feature::F_DELETE])) {
					unset($result[Feature::F_DELETE]);
				}
			}
		}
	}

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * @param $object    Lockable
	 * @param $joinpoint After_Method
	 */
	public function afterOutputControllerGetGeneralButtons($object, After_Method $joinpoint)
	{
		if (isA($object, Lockable::class)) {
			$buttons =& $joinpoint->result;
			/** @var $object Lockable */
			// if the object is locked : remove edit and delete buttons
			if ($object->locked) {
				if (isset($buttons[Feature::F_EDIT])) {
					if (isA($object, Duplicate::class)) {
						if ($buttons[Feature::F_EDIT]->sub_buttons[Feature::F_DUPLICATE]) {
							$buttons[Feature::F_EDIT] = $buttons[Feature::F_EDIT]->sub_buttons[Feature::F_DUPLICATE];
						}
					}
					else {
						unset($buttons[Feature::F_EDIT]);
					}
				}
				if (isset($buttons[Feature::F_DELETE])) {
					unset($buttons[Feature::F_DELETE]);
				}
			}
			// if the object is not locked : add lock button
			elseif (!isA($joinpoint->pointcut[0], Edit\Controller::class)) {
				if (!isset($buttons[Controller::FEATURE])) {
					$buttons[Controller::FEATURE] = new Button(
						'Lock',
						View::link($object, Controller::FEATURE),
						Controller::FEATURE,
						[new Color(Color::GREEN), Target::MESSAGES]
					);
				}
			}
		}
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
		$aop->afterMethod(
			[Edit\Controller::class, 'getGeneralButtons'],
			[$this, 'afterEditControllerGetGeneralButtons']
		);
		$aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'],
			[$this, 'afterOutputControllerGetGeneralButtons']
		);
	}

}
