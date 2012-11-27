<?php
namespace SAF\Framework;

class Trashcan_Drop_Controller implements Feature_Controller
{

	//------------------------------------------------------------------------------------------- run
	/**
	 * This will be called for this controller, always.
	 *
	 * @param Controller_Parameters $parameters
	 * @param array  $form
	 * @param array  $files
	 */
	public function run(Controller_Parameters $parameters, $form, $files)
	{
		$objects = $parameters->getObjects();
		if (is_object($objects[0])) {
			$object = $objects[0];
			Dao::delete($object);
			echo Names::classToDisplay(get_class($object)) . " was deleted";
		}
		else {
			list($class_name, $feature, $element) = $parameters->getObjects();
			Main_Controller::getInstance()->runController(
				"/" . $class_name . "/" . $feature . "Remove/" . $element
			);
		}
	}

}
