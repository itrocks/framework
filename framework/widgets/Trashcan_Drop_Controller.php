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
			$class_name = array_shift($objects);
			$feature    = array_shift($objects);
			foreach ($objects as $key => $value) {
				if (!is_numeric($key)) {
					unset($objects[$key]);
					$get[$key] = $value;
				}
			}
			$elements = join("/", $objects);
			Main_Controller::getInstance()->runController(
				"/" . $class_name . "/" . $feature . "Remove/" . $elements, $form
			);
		}
	}

}
