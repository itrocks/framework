<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Lock\Controller;
use ITRocks\Framework\Feature\Lock\Lockable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
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
		if (!isA($object, Lockable::class) || !$object->locked) {
			return;
		}
		if ($object->locked) {
			if (isset($result[Feature::F_SAVE])) {
				unset($result[Feature::F_SAVE]);
			}
			if (isset($result[Feature::F_DELETE])) {
				unset($result[Feature::F_DELETE]);
			}
		}
	}

	//-------------------------------------------------------- afterListControllerGetSelectionButtons
	/**
	 * @param $class_name string
	 * @param $result     Button[]
	 */
	public function afterListControllerGetSelectionButtons($class_name, array &$result)
	{
		if (!isA($class_name, Lockable::class)) {
			return;
		}
		$lock_button = new Button(
			'Lock',
			View::link($class_name, Controller::FEATURE),
			Controller::FEATURE,
			Target::RESPONSES
		);
		if (!isset($result[Feature::F_DELETE])) {
			$result[Controller::FEATURE] = $lock_button;
			return;
		}
		$buttons = [];
		foreach ($result as $key => $button) {
			if ($key === Feature::F_DELETE) {
				$buttons[Controller::FEATURE] = $lock_button;
			}
			$buttons[$key] = $button;
		}
		$result = $buttons;
	}

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * @param $object    Lockable
	 * @param $joinpoint After_Method
	 */
	public function afterOutputControllerGetGeneralButtons($object, After_Method $joinpoint)
	{
		if (!isA($object, Lockable::class)) {
			return;
		}
		/** @var $buttons Button[] */
		$buttons =& $joinpoint->result;
		if ($object->locked) {
			if (isset($buttons[Feature::F_EDIT])) {
				unset($buttons[Feature::F_EDIT]);
			}
			if (isset($buttons[Feature::F_DELETE])) {
				unset($buttons[Feature::F_DELETE]);
			}
		}
		elseif (
			!isA($joinpoint->pointcut[0], Edit\Controller::class)
			&& !isset($buttons[Controller::FEATURE])
		) {
			$buttons[Controller::FEATURE] = new Button(
				'Lock',
				View::link($object, Controller::FEATURE),
				Controller::FEATURE,
				Target::RESPONSES
			);
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
			[List_\Controller::class, 'getSelectionButtons'],
			[$this, 'afterListControllerGetSelectionButtons']
		);
		$aop->afterMethod(
			[Output\Controller::class, 'getGeneralButtons'],
			[$this, 'afterOutputControllerGetGeneralButtons']
		);
	}

}
