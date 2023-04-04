<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Attribute\Class_\List_;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\User\Access_Control;

/**
 * List class annotation access control plugin
 *
 * - Always remove or force lock option into #List_ depending on low-level features :
 *   alwaysLockColumns : it is as through you have set the lock option to all #List_ attributes
 *   neverLockColumns : it is as through you have never set the lock option to #List_ attributes
 *
 * @needs Access_Control
 */
class List_Annotation_Access_Control implements Registerable
{

	//---------------------------------------------------------- checkListAnnotationLockColumnsAccess
	/**
	 * Check if List_::$lock must be forced or removed
	 *
	 * If both alwaysLockColumns and neverLockColumns are set, always wins (super-administrator case)
	 */
	public function checkListAnnotationLockColumnsAccess(Reflection_Class $class, List_ $object)
		: void
	{
		$class_name = Builder::className($class->getName());
		if (!($access_control = Access_Control::get(false))) {
			return;
		}
		if ($object->lock && $access_control->hasAccessTo([$class_name, 'neverLockColumns'])) {
			$object->lock = false;
		}
		elseif (
			!$object->lock
			&& $access_control->hasAccessTo([$class_name, 'alwaysLockColumns'])
			&& !$access_control->hasAccessTo([$class_name, 'neverLockColumns'])
		) {
			$object->lock = true;
		}
	}

	//-------------------------------------------------------------------------------------- register
	public function register(Register $register) : void
	{
		$register->aop->afterMethod(
			[List_::class, '__construct'], [$this, 'checkListAnnotationLockColumnsAccess']
		);
	}

}
