<?php
namespace SAF\Framework\Widget\Delete_And_Replace;

use SAF\Framework\Controller\Default_Feature_Controller;
use SAF\Framework\Controller\Parameters;
use SAF\Framework\Dao;
use SAF\Framework\View;
use SAF\Framework\Widget\Delete_And_Replace;

/**
 * Default delete-and-replace controller
 */
class Delete_And_Replace_Controller implements Default_Feature_Controller
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
		$replaced = $parameters->getMainObject($class_name);
		$objects = $parameters->getObjects();
		if ($id_replace_with = $parameters->getRawParameter('id_replace_with')) {
			$objects['replace_with'] = $replacement = Dao::read($id_replace_with, $class_name);
			Dao::begin();
			if ((new Delete_And_Replace())->deleteAndReplace($replaced, $replacement)) {
				Dao::commit();
				$objects['done'] = true;
			}
			else {
				Dao::rollback();
				$objects['error'] = true;
			}
		}
		return View::run($objects, $form, $files, $class_name, 'deleteAndReplace');
	}

}
