<?php
namespace ITRocks\Framework\Feature\Delete;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * The default delete controller will be called if no other delete controller is defined
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- CONFIRM
	const CONFIRM = 'confirm';

	//--------------------------------------------------------------------------------------- confirm
	/**
	 * Confirmation form
	 *
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	protected function confirm(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters->set(
			'delete_link',
			View::link($parameters->getMainObject(), Feature::F_DELETE, null, static::CONFIRM)
		);
		$parameters->set('close_link', View::link($parameters->getMainObject()));
		$parameters = $parameters->getObjects();
		$parameters[Template::TEMPLATE] = static::CONFIRM;
		return View::run($parameters, $form, $files, $class_name, Feature::F_DELETE);
	}

	//---------------------------------------------------------------------------------------- delete
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	protected function delete(Parameters $parameters, $form, $files, $class_name)
	{
		$parameters      = $parameters->getObjects();
		$deleted_objects = [];
		foreach ($parameters as $object) {
			if (is_object($object)) {
				$deleted_objects[] = $object;
			}
		}

		$deleted_objects = $this->deleteObjects($deleted_objects);

		$parameters['deleted']         = $deleted_objects ? true : false;
		$parameters['deleted_objects'] = $deleted_objects;

		// avoid side effects between object and parameters
		if (is_object(reset($parameters))) {
			unset(reset($parameters)->deleted);
			unset(reset($parameters)->deleted_objects);
		}

		return View::run($parameters, $form, $files, $class_name, Feature::F_DELETE);
	}

	//--------------------------------------------------------------------------------- deleteObjects
	/**
	 * Delete objects in one transaction
	 *
	 * If at least one object could not delete, none of the objects will be deleted
	 * The deleted objects list will be returned as empty if deletion is cancelled
	 *
	 * @param $deleted_objects object[]
	 * @return object[]
	 */
	protected function deleteObjects(array $deleted_objects)
	{
		Dao::begin();
		foreach ($deleted_objects as $object) {
			if (!Dao::delete($object)) {
				Dao::rollback();
				return [];
			}
		}
		Dao::commit();
		return $deleted_objects;
	}

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array[]
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, array $form, array $files, $class_name)
	{
		return $parameters->has(static::CONFIRM, true)
			? $this->delete($parameters, $form, $files, $class_name)
			: $this->confirm($parameters, $form, $files, $class_name);
	}

}
