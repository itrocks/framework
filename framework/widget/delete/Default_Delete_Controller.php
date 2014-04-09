<?php
namespace SAF\Framework\Widget\Delete;

use SAF\Framework\Controller\Default_Controller;
use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao;

/**
 * The default delete controller will be called if no other delete controller is defined
 */
class Default_Delete_Controller implements Default_Feature_Controller
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
		$objects = $parameters->getObjects();
		Dao::begin();
		foreach ($objects as $object) {
			if (is_object($object)) {
				Dao::delete($object);
			}
		}
		Dao::commit();
		return (new Default_Controller)->run(
			$parameters, $form, $files, $class_name, 'deleted'
		);
	}

}
