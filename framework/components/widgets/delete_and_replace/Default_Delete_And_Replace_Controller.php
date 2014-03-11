<?php
namespace SAF\Framework;

/**
 * Default delete-and-replace controller
 */
class Default_Delete_And_Replace_Controller implements Default_Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * @param $parameters Controller_Parameters
	 * @param $form       array
	 * @param $files      array
	 * @param $class_name string
	 * @return mixed
	 */
	public function run(Controller_Parameters $parameters, $form, $files, $class_name)
	{
		$replaced = $parameters->getMainObject($class_name);
		$objects = $parameters->getObjects();
		if ($id_replace_with = $parameters->getRawParameter('id_replace_with')) {
			$objects['replace_with'] = $replacement = Dao::read($id_replace_with, $class_name);
			(new Delete_And_Replace())->deleteAndReplace($replaced, $replacement);
		}
		return View::run($objects, $form, $files, $class_name, 'deleteAndReplace');
	}

}
