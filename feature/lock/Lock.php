<?php
namespace ITRocks\Framework\Feature;

use ITRocks\Framework\AOP\Joinpoint\After_Method;
use ITRocks\Framework\Component\Button;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Controller\Target;
use ITRocks\Framework\Feature\Lock\Controller;
use ITRocks\Framework\Feature\Lock\Lockable;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Reflection_Class;
use ITRocks\Framework\Reflection\Reflection_Property;
use ITRocks\Framework\View;

/**
 * The lock plugin enables to lock any object
 */
class Lock implements Registerable
{

	//---------------------------------------------------------- afterEditControllerGetGeneralButtons
	/**
	 * @noinspection PhpDocSignatureInspection filtered by Lockable
	 * @param $object object|Lockable
	 * @param $result Button[]
	 */
	public function afterEditControllerGetGeneralButtons(object $object, array &$result) : void
	{
		if (!isA($object, Lockable::class) || !$object->locked) {
			return;
		}
		if (isset($result[Feature::F_SAVE]) && !static::unlockedProperties($object)) {
			unset($result[Feature::F_SAVE]);
		}
		if (isset($result[Feature::F_DELETE])) {
			unset($result[Feature::F_DELETE]);
		}
	}

	//-------------------------------------------------------- afterListControllerGetSelectionButtons
	/**
	 * @param $class_name string
	 * @param $result     Button[]
	 */
	public function afterListControllerGetSelectionButtons(string $class_name, array &$result) : void
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
		Button::insertBefore($result, $lock_button, Feature::F_DELETE);
	}

	//-------------------------------------------------------- afterOutputControllerGetGeneralButtons
	/**
	 * @noinspection PhpDocSignatureInspection filtered by Lockable
	 * @param $object    object|Lockable
	 * @param $joinpoint After_Method
	 */
	public function afterOutputControllerGetGeneralButtons(object $object, After_Method $joinpoint)
		: void
	{
		if (!isA($object, Lockable::class)) {
			return;
		}
		/** @var $buttons Button[] */
		$buttons =& $joinpoint->result;
		if ($object->locked) {
			if (isset($buttons[Feature::F_EDIT]) && !static::unlockedProperties($object)) {
				unset($buttons[Feature::F_EDIT]);
			}
			if (isset($buttons[Feature::F_EDIT])) {
				$buttons[Feature::F_EDIT]->addOption(Target::POPUP, View::TARGET);
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

	//--------------------------------------------------------- beforeEditControllerGetViewParameters
	/**
	 * @param $parameters Parameters
	 */
	public function beforeEditControllerGetViewParameters(Parameters $parameters) : void
	{
		/** @var $object Lockable */
		$object = $parameters->getMainObject();
		if (isA($object, Lockable::class) && $object->locked) {
			$parameters->set('only', ['@modifiable', '@unlocked']);
		}
	}

	//------------------------------------------------------ beforeOutputControllerOnlyPropertiesAuto
	/**
	 * @param $properties_filter string[]
	 * @param $auto              string[]
	 * @param $properties        Reflection_Property[]
	 */
	public function beforeOutputControllerOnlyPropertiesAuto(
		array &$properties_filter, array $auto, array $properties
	) : void
	{
		if (!isset($auto['@unlocked'])) {
			return;
		}
		foreach ($properties_filter as $key => $property_name) {
			if ($properties[$property_name]->getAnnotation('unlocked')->value) {
				continue;
			}
			unset($properties_filter[$key]);
		}
	}

	//-------------------------------------------------------------------------------------- register
	/**
	 * Registration code for the plugin
	 *
	 * @param $register Register
	 */
	public function register(Register $register) : void
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
		$aop->beforeMethod(
			[Edit\Controller::class, 'getViewParameters'],
			[$this, 'beforeEditControllerGetViewParameters']
		);
		$aop->beforeMethod(
			[Output\Controller::class, 'onlyPropertiesAuto'],
			[$this, 'beforeOutputControllerOnlyPropertiesAuto']
		);
	}

	//---------------------------------------------------------------------------- unlockedProperties
	/**
	 * @noinspection PhpDocMissingThrowsInspection
	 * @param $object object
	 * @return string[]
	 */
	public static function unlockedProperties(object $object) : array
	{
		/** @noinspection PhpUnhandledExceptionInspection object */
		$properties     = (new Reflection_Class($object))->getProperties();
		$property_names = array_keys($properties);
		(new Output\Controller)->onlyPropertiesAuto(
			$property_names, ['@modifiable' => true, '@unlocked' => true], $properties
		);
		return $property_names;
	}

}
