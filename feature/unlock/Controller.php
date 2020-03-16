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
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files)
	{
		$object = $parameters->getMainObject();
		if (is_a($object, Unlockable::class)) {
			/** @var $object Lockable */
			$object->locked = false;
			Dao::begin();
			Dao::write($object, Dao::only('locked'));
			Dao::commit();
		}

		$parameters                       = $parameters->getObjects();
		$parameters[Parameters::REDIRECT] = View::link($object);

		return View::run($parameters, $form, $files, get_class($object), static::FEATURE);
	}

}
