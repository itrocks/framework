<?php
namespace ITRocks\Framework\Feature\Unlock;

use ITRocks\Framework\Controller\Feature_Controller;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Feature\Lock\Lockable;
use ITRocks\Framework\View;

/**
 * Object lock controller
 */
class Controller implements Feature_Controller
{

	//--------------------------------------------------------------------------------------- FEATURE
	const FEATURE = 'unlock';

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @return ?string
	 */
	public function run(Parameters $parameters, array $form, array $files) : ?string
	{
		/** @var $objects Lockable[] */
		$objects = $parameters->getSelectedObjects($form);
		if (!$objects) {
			return null;
		}
		Dao::begin();
		foreach ($objects as $object) {
			if ($object instanceof Unlockable) {
				/** @var $object Lockable|Unlockable */
				$object->locked = false;
				Dao::write($object, Dao::only('locked'));
			}
		}
		Dao::commit();
		$parameters->set('objects', $objects);
		return View::run(
			$parameters->getObjects(), $form, $files, get_class(reset($objects)), static::FEATURE
		);
	}

}
