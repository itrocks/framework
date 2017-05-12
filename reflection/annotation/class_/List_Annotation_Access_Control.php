<?php
namespace ITRocks\Framework\Reflection\Annotation\Class_;

use ITRocks\Framework\Plugin\Register;
use ITRocks\Framework\Plugin\Registerable;
use ITRocks\Framework\Reflection\Interfaces\Reflection_Class;
use ITRocks\Framework\User\Access_Control;

/**
 * List class annotation access control plugin
 *
 * - Always remove or force lock option into @list depending on low-level features :
 *   alwaysLockColumns : it is as through you have set the lock option to all @list annotations
 *   neverLockColumns : it is as through you have never set the lock option to @list annotations
 *
 * @needs Access_Control
 */
class List_Annotation_Access_Control implements Registerable
{

	//---------------------------------------------------------- checkListAnnotationLockColumnsAccess
	/**
	 * Check if List_Annotation::has(List_Annotation::LOCK) must be forced or removed
	 *
	 * If both alwaysLockColumns and neverLockColumns are set, always wins (super-administrator case)
	 *
	 * @param $class  Reflection_Class
	 * @param $object List_Annotation
	 */
	public function checkListAnnotationLockColumnsAccess(
		Reflection_Class $class, List_Annotation $object
	) {
		if ($access_control = Access_Control::get()) {
			if (
				$object->has(List_Annotation::LOCK)
				&& $access_control->hasAccessTo([$class->getName(), 'neverLockColumns'])
			) {
				$object->remove(List_Annotation::LOCK);
			}
			elseif (
				!$object->has(List_Annotation::LOCK)
				&& $access_control->hasAccessTo([$class->getName(), 'alwaysLockColumns'])
				&& !$access_control->hasAccessTo([$class->getName(), 'neverLockColumns'])
			) {
				$object->add(List_Annotation::LOCK);
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
		$register->aop->afterMethod(
			[List_Annotation::class, '__construct'], [$this, 'checkListAnnotationLockColumnsAccess']
		);
	}

}
