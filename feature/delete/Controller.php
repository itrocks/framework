<?php
namespace ITRocks\Framework\Feature\Delete;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Locale\Loc;
use ITRocks\Framework\Tools\Names;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * The default delete controller will be called if no other delete controller is defined
 */
class Controller implements Default_Feature_Controller
{

	//--------------------------------------------------------------------------------------- CONFIRM
	const CONFIRM = 'confirm';

	//-------------------------------------------------------------------------------------- $objects
	/**
	 * Objects to delete. Key is the object identifier
	 *
	 * @var object[]
	 */
	protected $objects;

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
		$link = $parameters->uri->uri;
		$link .= (strpos($link, '?') ? '&' : '?') . static::CONFIRM;
		$parameters->set('delete_link', $link);
		$parameters->set('close_link', View::link($parameters->getMainObject()));

		if ($form) {
			$parameters->set('data_post', http_build_query($form));
			if (!Dao::getObjectIdentifier($parameters->getMainObject())) {
				$parameters->unshift(new Multiple($this->objects));
			}
		}

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
		$deleted_objects = $this->deleteObjects($this->objects);

		$parameters['deleted']         = $deleted_objects ? true : false;
		$parameters['deleted_objects'] = $deleted_objects;

		// avoid side effects between object and parameters
		if (is_object(reset($parameters))) {
			unset(reset($parameters)->deleted);
			unset(reset($parameters)->deleted_objects);
			$parameters['set_class'] = Names::classToSet(
				Builder::current()->sourceClassName($class_name)
			);
		}

		$parameters['message'] = $this->message($deleted_objects, $class_name);

		return View::run($parameters, $form, $files, $class_name, Feature::F_DELETE);
	}

	//--------------------------------------------------------------------------------- deleteObjects
	/**
	 * Delete objects in one transaction
	 *
	 * If at least one object could not delete, none of the objects will be deleted
	 * The deleted objects list will be returned as empty if deletion is cancelled
	 *
	 * @param $deleted_objects object[] key is the identifier of the object
	 * @return object[] key is the identifier of the object
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

	//--------------------------------------------------------------------------------------- message
	/**
	 * @param $deleted_objects object[]
	 * @param $class_name      string
	 * @return string
	 */
	protected function message(array $deleted_objects, $class_name)
	{
		$count = count($deleted_objects);
		$class = Loc::tr(
			($count > 1) ? Names::classToDisplays($class_name) : Names::classToDisplay($class_name)
		);
		$object = ($count === 1) ? strval(reset($deleted_objects)) : null;
		if ($deleted_objects) {
			if ($count > 1) {
				$message = Loc::tr(
					':count :classes have been deleted',
					Loc::replace(['classes' => $class, 'count' => $count])
				);
			}
			else {
				$message = Loc::tr(
					':class :object has been deleted',
					Loc::replace(['class' => $class, 'object' => $object])
				);
			}
		}
		else {
			$count = count($this->objects);
			if ($count > 1) {
				$message = Loc::tr(
					'unable to delete :count :classes',
					Loc::replace(['classes' => $class, 'count' => $count])
				);
			}
			else {
				$message = Loc::tr(
					'unable to delete :class :object',
					Loc::replace(['class' => $class, 'object' => $object])
				);
			}
		}
		return $message;
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
		$this->objects = $parameters->getSelectedObjects($form);
		if (!$this->objects) {
			return Loc::tr('You must select at least one element');
		}
		return $parameters->has(static::CONFIRM, true)
			? $this->delete($parameters,  $form, $files, $class_name)
			: $this->confirm($parameters, $form, $files, $class_name);
	}

}
