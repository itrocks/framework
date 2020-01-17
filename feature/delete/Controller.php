<?php
namespace ITRocks\Framework\Feature\Delete;

use ITRocks\Framework\Builder;
use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\Dao\Mysql\Information_Schema\Lock_Information;
use ITRocks\Framework\Dao\Mysql\Mysql_Error_Exception;
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

	//------------------------------------------------------------------------------------- EXCEPTION
	const EXCEPTION = 'exception';

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

		$multiple = false;
		if ($form) {
			$parameters->set('data_post', http_build_query($form));
			if (!Dao::getObjectIdentifier($parameters->getMainObject())) {
				$parameters->unshift(new Multiple($this->objects));
				$multiple = true;
			}
		}
		else {
			$parameters->set('data_post', null);
		}
		$parameters->set('multiple', $multiple);

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
		$parameters = $parameters->getObjects();

		try {
			$deleted_objects = $this->deleteObjects($this->objects);
			$deleted         = ($deleted_objects ? true : false);
		}
		/** @noinspection PhpRedundantCatchClauseInspection This may be thrown */
		catch (Mysql_Error_Exception $exception) {
			$deleted_objects = $this->objects;
			$deleted         = false;
		}
		$parameters['deleted']         = $deleted;
		$parameters['deleted_objects'] = $this->objects;

		// avoid side effects between object and parameters
		if (is_object(reset($parameters))) {
			unset(reset($parameters)->deleted);
			unset(reset($parameters)->deleted_objects);
			$parameters['set_class'] = Names::classToSet(
				Builder::current()->sourceClassName($class_name)
			);
		}

		$parameters['display_deleted_objects'] = (count($deleted_objects) > 1);

		$parameters['message'] = $this->message($deleted_objects, $class_name, $deleted);
		if (!$deleted) {
			$parameters['locked_objects']   = $this->lockedObjects($deleted_objects);
			$parameters[Template::TEMPLATE] = static::EXCEPTION;
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

	//--------------------------------------------------------------------------------- lockedObjects
	/**
	 * Keeps only locked objects from the list
	 * Associate each of these locked objects to their locking objects list
	 *
	 * @param $objects object[]
	 * @return array ['object' => $locked_object object, 'lock_objects' => $lock_objects Lock_Objects]
	 */
	protected function lockedObjects(array $objects)
	{
		$locked_objects = [];
		foreach ($objects as $object) {
			$lock_objects = (new Lock_Information)->whoLocks($object);
			if (!$lock_objects) {
				continue;
			}
			$locked_objects[] = ['object' => $object, 'lock_objects' => $lock_objects];
		}
		return $locked_objects;
	}

	//--------------------------------------------------------------------------------------- message
	/**
	 * @param $objects    object[]
	 * @param $class_name string
	 * @param $deleted    boolean
	 * @return string
	 */
	protected function message(array $objects, $class_name, $deleted)
	{
		$count = count($objects);
		$class = Loc::tr(
			($count > 1) ? Names::classToDisplays($class_name) : Names::classToDisplay($class_name)
		);
		$object = ($count === 1) ? strval(reset($objects)) : null;
		if ($deleted && $objects) {
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
			return '<!--target #query--><li>'
				. Loc::tr('You must select at least one element')
				. '</li><!--end-->';
		}
		return $parameters->has(static::CONFIRM, true)
			? $this->delete($parameters,  $form, $files, $class_name)
			: $this->confirm($parameters, $form, $files, $class_name);
	}

}
