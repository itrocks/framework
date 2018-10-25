<?php
namespace ITRocks\Framework\Widget\Delete;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\View;
use ITRocks\Framework\View\Html\Template;

/**
 * The default delete controller will be called if no other delete controller is defined
 */
class Delete_Controller implements Default_Feature_Controller
{

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
			View::link($parameters->getMainObject(), Feature::F_DELETE, null, 'confirm')
		);
		$parameters->set('close_link', View::link($parameters->getMainObject()));
		$parameters = $parameters->getObjects();
		$parameters[Template::TEMPLATE] = 'confirm';
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

		Dao::begin();
		$deleted = 0;
		foreach ($parameters as $object) {
			if (is_object($object)) {
				if (!Dao::delete($object)) {
					$deleted = 0;
					break;
				}
				$deleted ++;
			}
		}
		Dao::commit();

		$parameters['deleted'] = $deleted ? true : false;
		return View::run($parameters, $form, $files, $class_name, Feature::F_DELETE);
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
		return $parameters->has('confirm')
			? $this->delete($parameters, $form, $files, $class_name)
			: $this->confirm($parameters, $form, $files, $class_name);
	}

}
