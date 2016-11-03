<?php
namespace ITRocks\Framework\Widget\Delete;

use ITRocks\Framework\Controller\Default_Feature_Controller;
use ITRocks\Framework\Controller\Feature;
use ITRocks\Framework\Controller\Parameters;
use ITRocks\Framework\Dao;
use ITRocks\Framework\View;

/**
 * The default delete controller will be called if no other delete controller is defined
 */
class Delete_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Parameters $parameters, $form, $files, $class_name)
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

}
